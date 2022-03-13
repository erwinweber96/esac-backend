<?php


namespace Modules\User\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Console\Traits\RequiresConsoleAccess;
use Modules\User\Entities\ChallengeSettings;
use Modules\User\Entities\User;
use Modules\User\Entities\UserMessage;
use Modules\User\Entities\UserNotification;
use Modules\User\Events\NewUserMessageSent;

/**
 * Class UserMessageController
 * @package Modules\User\Http\Controllers
 */
class UserMessageController
{
    use RequiresConsoleAccess;

    public function getByChannel($channel)
    {
        if (!$this->authorizeChannel($channel)) {
            return response()->json([
                "errors" => ["message" => "Not Authorized"]
            ], Response::HTTP_UNAUTHORIZED);
        }

        return UserMessage::where("channel", $channel)
            ->orderBy("id", "desc")
            ->paginate(10);
    }

    public function create(Request $request)
    {
        //authorize channel
        if (!$this->authorizeChannel($request->input("channel"))) {
            return response()->json([
                "errors" => ["message" => "Not Authorized"]
            ], Response::HTTP_UNAUTHORIZED);
        }

        /** @var User $user */
        $user = auth()->user();

        if ($request->input("type") == UserMessage::TYPE_CHALLENGE) {
            $this->verifyConsoleAccess();

            /** @var ChallengeSettings $challengeSettings */
            $challengeSettings = json_decode($request->input('message'));

            if ($user->coins < $challengeSettings->coins) {
                return response()->json(["errors" => [
                    "message" => "Not enough coins."
                ]], Response::HTTP_BAD_REQUEST);
            }

            $notification = new UserNotification();

            $notification->title   = "You have been challenged!";
            $notification->message = $user->nickname." created a 1v1 challenge.";
            $notification->userId  = $request->input("toUserId");
            $notification->url     = "https://esac.gg/messages/".$user->id;
            $notification->variant = "primary";

            $notification->save();
        }

        //create message
        $message = new UserMessage();

        $message->channel = $request->input("channel");
        $message->fromUserId = $request->input("fromUserId");
        $message->toUserId = $request->input("toUserId");
        $message->message = $request->input("message");
        $message->type = $request->input("type");

        $message->save();

        return $message;
    }

    public function authorizeChannel($channel)
    {
        $channelArray = explode("_", $channel);

        $fromUserId = $channelArray[1];
        $toUserId   = $channelArray[2];

        //authorize channel
        /** @var User $user */
        $user = auth()->user();

        if ($user->id != $fromUserId && $user->id != $toUserId) {
            return false;
        }

        $friendId = $fromUserId == $user->id ? $toUserId : $fromUserId;

        $friends = $user->getFriends()->filter(function (User $friend) use ($friendId) {
            return $friend->id == $friendId;
        });

        if (!$friends->count()) {
            return false;
        }

        return true;
    }
}
