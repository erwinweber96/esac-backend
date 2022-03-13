<?php

namespace Modules\User\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse as JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Modules\Event\Entities\Event;
use Modules\Market\Entities\Badge;
use Modules\Page\Entities\PageMember;
use Modules\Page\Entities\PageMemberRole;
use Modules\User\Builders\UserBuilder;
use Modules\User\Entities\ResetToken;
use Modules\User\Entities\User;
use Modules\User\Entities\UserRole;
use Modules\User\Events\ResetTokenGenerated;
use Modules\User\Http\Requests\ResetPasswordRequest;
use Modules\User\Http\Requests\UpdateUserPasswordRequest;
use Modules\User\Http\Requests\UpdateUserProfileRequest;
use Modules\User\Http\Requests\UserRegistrationRequest;
use Modules\User\Repositories\UserRepository;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Class UserController
 * @package Modules\User\Http\Controllers
 */
class UserController extends Controller
{
    /** @var UserRepository $userRepository */
    private $userRepository;

    /**
     * UserController constructor.
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }


    /**
     * @param UserRegistrationRequest $request
     * @return JsonResponse
     */
    public function register(UserRegistrationRequest $request)
    {
        $validate = $request->validated();

        /** @var UserBuilder $userBuilder */
        $userBuilder = (new UserBuilder())->prepare();

        $userBuilder
            ->setEmail($request->input("email"))
            ->setNat($request->input("nationality"))
            ->setPassword($request->input("password"))
            ->setNickname($request->input("nickname"));

        /** @var User $user */
        $user = $this->userRepository->create($userBuilder);

        UserRole::create([
           "user_id" => $user->id,
            "role" => UserRole::CREATE_PAGE
        ]);

        return response()->json($user, Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request)
    {
        $email = $request->input("email");
        $password = $request->input("password");

        $token = JWTAuth::attempt([
            "email" => $email,
            "password" => $password
        ]);

        if (!$token) {
            return response()->json([
                "error" => "Invalid email/password combination."
            ], Response::HTTP_UNAUTHORIZED);
        }

        return response()->json(["token" => $token], Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request)
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
        } catch (\Throwable $exception) {
            return response()->json(["error" => "Could not logout."], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json(["message" => "Logged out successfully."], Response::HTTP_OK);
    }

    /**
     * @return JsonResponse
     */
    public function getUserPages()
    {
        $userId = auth()->id();

        /** @var User $user */
        $user = $this->userRepository->show($userId);

        return response()->json([
            "pages" => $user->pages()->with(['type'])->get()
        ], Response::HTTP_OK);
    }

    public function getUserDetails()
    {
        $userId = auth()->id();
        return User::where("id", $userId)
            ->with('discord')
            ->with('maniaplanet')
            ->with('twitchAccessToken')
            ->with('trackmania')
            ->first()
            ->makeVisible(['email']);
    }

    public function updateUserProfile(UpdateUserProfileRequest $request)
    {
        $request->validated();
        return User::where("id", auth()->id())->update($request->toArray());
    }

    public function updateUserPassword(UpdateUserPasswordRequest $request)
    {
        $request->validated();

        /** @var UserBuilder $userBuilder */
        $userBuilder = (new UserBuilder())->prepare();

        $built = $userBuilder->setPassword($request->password)->build();

        return User::where("id", auth()->id())->update($built);
    }

    public function getAuthUser()
    {
        /** @var User $user */
        $user = User::where("id", auth()->user()->id)
            ->with('pages')
            ->with('roles')
            ->with('discord')
            ->with('sentFriendRequests')
            ->with('receivedFriendRequests')
            ->with("caseDrops")
            ->with("badges")
            ->with("coinTransactions")
            ->first();

        $user->makeVisible(["coins"]);
        return $user;
    }

    public function getUserById($userId)
    {
        /** @var User $user */
        $user = User::where("id", $userId)
            ->with('pages')
            ->with('badges')
            ->with("participants")
            ->with('participants.groupResults')
            ->with('participants.groupResults.group')
            ->first();

        if ($user->participants->count()) {
            foreach ($user->participants as &$participant) {
                $participant->event = DB::table(Event::TABLE_NAME)
                    ->where("id", $participant->eventId)
                    ->first(["name", "type", "slug"]);
            }
        }

        return $user;
    }

    public function getUserEvents()
    {
        /** @var User $user */
        $user = auth()->user();

        $pageIds = $user->pages->map(function($page) {
            return $page->id;
        });

        return Event::whereIn("page_id", $pageIds->toArray())->get();
    }

    public function updateTmNickname(Request $request)
    {
        /** @var User $user */
        $user = auth()->user();

        $user->tmNickname = $request->input("tmNickname");
        return $user->save();
    }

    public function getMemberPages()
    {
        /** @var User $user */
        $user = auth()->user();

        return PageMember::where("user_id", $user->id)
            ->with("page")
            ->get();
    }

    public function leavePage($pageId)
    {
        /** @var User $user */
        $user = auth()->user();

        /** @var PageMember $pageMember */
        $pageMember = PageMember::where("page_id", $pageId)
            ->where("user_id", $user->id)
            ->first();

        try {
            PageMemberRole::where("member_id", $pageMember->id)->delete();
            $pageMember->delete();
        } catch (\Throwable $exception) {
            return response()->json([
                "errors" => ["message" => "Could not leave page."]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json(["message" => "Successfully left page.", Response::HTTP_OK]);
    }

    public function sendResetLink(Request $request)
    {
        $email = $request->input("email");

        /** @var User $user */
        $user = User::where("email", $email)->first();

        if (!$user) {
            return;
        }

        $resetToken             = new ResetToken();
        $resetToken->email      = $email;
        $resetToken->token      = Str::random("16");
        $resetToken->expires    = Carbon::now()->addMinutes(5);

        $resetToken->save();
        Mail::to($email)->send(new ResetTokenGenerated($resetToken));
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $request->validated();

        /** @var ResetToken $resetToken */
        $resetToken = ResetToken::where("token", $request->token)->first();

        if (!$resetToken) {
            return response()->json([
                "errors" => ["message" => ["Reset password was not requested or the request is corrupted."]]
            ], Response::HTTP_BAD_REQUEST);
        }

        $expires = new Carbon($resetToken->expires);

        if (Carbon::now()->timestamp > $expires->timestamp) {
            return response()->json([
                "errors" => ["message" => ["Reset password request has expired."]]
            ], Response::HTTP_BAD_REQUEST);
        }

        /** @var User $user */
        $user = User::where("email", $resetToken->email)->first();

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json(["message" => "Successfully reset password.", Response::HTTP_OK]);
    }

    public function attachBadge($badgeId)
    {
        /** @var User $user */
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                "errors" => ["message" => ["Not authenticated."]]
            ], Response::HTTP_BAD_REQUEST);
        }

        /** @var Badge $badge */
        $badge = Badge::where("id", $badgeId)->first();

        if (!$badge) {
            return response()->json([
                "errors" => ["message" => ["Badge not found."]]
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $user->badgeId = $badgeId;
            $user->save();
        } catch (\Throwable $exception) {
            return response()->json([
                "errors" => ["message" => ["Something went wrong. Could not attach badge."]]
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json(["message" => "Successfully attached badge."]);
    }
}
