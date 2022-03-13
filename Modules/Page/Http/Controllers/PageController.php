<?php

namespace Modules\Page\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Event\Entities\Event;
use Modules\Event\Entities\Participant;
use Modules\Group\Entities\Group;
use Modules\Match\Entities\MatchModel;
use Modules\Page\Builders\PageBuilder;
use Modules\Page\Builders\PageMemberBuilder;
use Modules\Page\Entities\Page;
use Modules\Page\Entities\PageMember;
use Modules\Page\Entities\PageMemberRole;
use Modules\Page\Entities\PageMemberType;
use Modules\Page\Entities\PageType;
use Modules\Page\Entities\TeamEloHistory;
use Modules\Page\Http\Requests\CreatePageRequest;
use Modules\Page\Http\Requests\UpdatePageRequest;
use Modules\Page\Repositories\PageMemberRepository;
use Modules\Page\Repositories\PageRepository;
use Modules\User\Entities\User;

/**
 * Class PageController
 * @package Modules\Page\Http\Controllers
 */
class PageController extends Controller
{
    /** @var PageRepository $pageRepository */
    private $pageRepository;

    /** @var PageMemberRepository $pageMemberRepository */
    private $pageMemberRepository;

    /**
     * PageController constructor.
     * @param PageRepository $pageRepository
     * @param PageMemberRepository $pageMemberRepository
     */
    public function __construct(
        PageRepository $pageRepository,
        PageMemberRepository $pageMemberRepository)
    {
        $this->pageRepository = $pageRepository;
        $this->pageMemberRepository = $pageMemberRepository;
    }

    /**
     * @param CreatePageRequest $request
     * @return JsonResponse
     */
    public function create(CreatePageRequest $request)
    {
        $request->validated();

        /** @var PageBuilder $pageBuilder */
        $pageBuilder = (new PageBuilder())->prepare();

        $pageBuilder
            ->setAbout($request->input("about"))
            ->setName($request->input("name"))
            ->setType($request->input("type_id"))
            ->withUserId(auth()->id());

        $page = $this->pageRepository->create($pageBuilder);

        $defaultRoles = [
            PageMemberRole::INVITE_MEMBERS,
            PageMemberRole::CREATE_EVENTS,
            PageMemberRole::SEE_PRIVATE_EVENTS,
            PageMemberRole::CREATE_POSTS
        ];

        $member = PageMember::create([
            "user_id" => auth()->user()->id,
            "page_id" => $page->id
        ]);

        foreach ($defaultRoles as $role) {
            PageMemberRole::create([
                "page_id" => $page->id,
                "member_id" => $member->id,
                "name" => $role,
            ]);
        }

        return response()->json($page, Response::HTTP_OK);
    }

    /**
     * @param UpdatePageRequest $request
     * @param $id
     * @return JsonResponse
     */
    public function update(UpdatePageRequest $request, $id)
    {
        $request->validated();

        /** @var PageBuilder $pageBuilder */
        $pageBuilder = (new PageBuilder())->prepare();

        $pageBuilder
            ->setName($request->input("name"), false)
            ->setAbout($request->input("about"))
            ->setType($request->input("type_id"))
            ->setPrivate($request->input("private"));

        $page = $this->pageRepository->update($pageBuilder, $id);
        return response()->json($page, Response::HTTP_OK);
    }

    public function getTypes()
    {
        return response()->json(PageType::get([
            'id as value',
            'text as text'
        ])->toArray(), Response::HTTP_OK);
    }

    public function get($slug)
    {
        /** @var Page $page */
        $page = $this->pageRepository->findBySlug($slug);
//
//        if ($page->type->id == PageType::VALUE_TEAM) {
//            $teamEloHistory = TeamEloHistory::where("page_id", $page->id)
//                ->with(TeamEloHistory::RELATIONS)
//                ->get();
//
//            $teamEloHistoryArray = $teamEloHistory->map(function (TeamEloHistory $eloEntry) {
//                $match = DB::table(Match::TABLE_NAME)
//                    ->where("id", $eloEntry->matchId)
//                    ->first(["name", "group_id"]);
//
//                $group = DB::table(Group::TABLE_NAME)
//                    ->where("id", $match->group_id)
//                    ->first(["event_id"]);
//
//                $event = DB::table(Event::TABLE_NAME)
//                    ->where("id", $group->event_id)
//                    ->first(["name", "slug"]);
//
//                $eloEntryArray          = $eloEntry->toArray();
//                $group->event           = $event;
//                $match->group           = $group;
//                $eloEntryArray['match'] = $match;
//
//                return $eloEntryArray;
//            });
//
//            $page = $page->toArray();
//            $page['team_elo_history'] = $teamEloHistoryArray;
//        }

        if ($page->participants->count()) {
            foreach ($page->participants as &$participant) {
                $participant->event = DB::table(Event::TABLE_NAME)
                    ->where("id", $participant->eventId)
                    ->first(["name", "type", "slug"]);
            }
        }

        return response()->json(
            $page,
            Response::HTTP_OK
        );
    }

    public function getPages()
    {
        return Page::where("private", false)
            ->with("type")
            ->get();
    }

    public function getPageNameByInviteToken($token)
    {
        return Page::where("invite_token", $token)->first();
    }

    public function getInviteToken($slug)
    {
        /** @var User $user */
        $user = auth()->user();

        /** @var Page $page */
        $page = $this->pageRepository->findBySlug($slug);

        if ($user->cannot(PageMemberRole::INVITE_MEMBERS, [$page]) && $user->id != $page->user->id) {
            return response()->json([
                "errors" => [
                    "message" => ["Not Authorized"]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }


        return $page->inviteToken;
    }

    public function acceptInvite(Request $request, $slug)
    {
        /** @var Page $page */
        $page  = Page::where("slug", $slug)
            ->with('members')
            ->first();

        $token = $request->input("token");

        if ($token != $page->inviteToken) {
            return response()->json([
                "errors" => ["message" => "Invalid invite token."]
            ], Response::HTTP_BAD_REQUEST);
        }

        /** @var User $user */
        $user = auth()->user();

        $userFound = $page->members->where("user_id", $user->id)->first();

        if ($userFound) {
            return response()->json([
                "errors" => ["message" => "You are already a member."]
            ], Response::HTTP_BAD_REQUEST);
        }

        /** @var PageMemberBuilder $builder */
        $builder = (new PageMemberBuilder())->prepare();

        $builder->setPageId($page->id)->setUserId($user->id)->build();
        $member = $this->pageMemberRepository->create($builder);

        if (!$member) {
            return response()->json([
                "errors" => ["message" => "Could not accept invite."]
            ], Response::HTTP_BAD_REQUEST);
        }

        return $member;
    }

    public function updateMemberRoles(Request $request, $slug, $memberId)
    {
        $roles = $request->toArray();

        /** @var Page $page */
        $page = $this->pageRepository->findBySlug($slug);

        if (!$page) {
            return response()->json([
                "error" => "Could not find page by given slug."
            ], Response::HTTP_NOT_FOUND);
        }

        /** @var User $user */
        $user = auth()->user();

        if ($user->id != $page->user->id) {
            return response()->json([
                "error" => "Not authorized to assign roles."
            ], Response::HTTP_UNAUTHORIZED);
        }

        foreach ($roles as $roleName => $allowed) {
            if (!in_array($roleName, PageMemberRole::ROLES)) {
                continue;
            }

            if ($allowed) {
                $exists = PageMemberRole::where("page_id", $page->id)
                    ->where("member_id", $memberId)
                    ->where("name", $roleName)
                    ->get();

                if (!$exists->count()) {
                    PageMemberRole::create([
                        "page_id" => $page->id,
                        "member_id" => $memberId,
                        "name" => $roleName,
                    ]);
                }
            } else {
                PageMemberRole::where("page_id", $page->id)
                    ->where("member_id", $memberId)
                    ->where("name", $roleName)
                    ->delete();
            }
        }

        return response()->json(["message" => "Successfully updated roles.", Response::HTTP_OK]);
    }

    public function removePageMember($memberId)
    {
        /** @var PageMember $pageMember */
        $pageMember = PageMember::where("id", $memberId)->first();

        if (!$pageMember) {
            return response()->json([
                "errors" => ["message" => "The requested member is not part of this page."]
            ], Response::HTTP_BAD_REQUEST);
        }

        /** @var User $user */
        $user = auth()->user();

        if ($user->id != $pageMember->page->user->id) {
            if ($user->id != $pageMember->user->id) {
                return response()->json([
                    "errors" => ["message" => "Not authorized."]
                ], Response::HTTP_UNAUTHORIZED);
            }
        }

        try {
            PageMemberRole::where("member_id", $pageMember->id)->delete();
            $pageMember->delete();
        } catch (\Throwable $exception) {
            return response()->json([
                "errors" => ["message" => "Could not delete member."]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json(["message" => "Successfully removed member.", Response::HTTP_OK]);
    }
}
