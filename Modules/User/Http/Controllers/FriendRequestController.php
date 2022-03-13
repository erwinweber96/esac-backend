<?php


namespace Modules\User\Http\Controllers;


use Illuminate\Http\Request;
use Modules\User\Entities\FriendRequest;
use Modules\User\Entities\User;
use Modules\User\Entities\UserNotification;

/**
 * Class FriendRequestController
 * @package Modules\User\Http\Controllers
 */
class FriendRequestController
{
    public function sendRequest(Request $request)
    {
        $fromUserId = auth()->user()->id;
        $toUserId = $request->input("userId");

        $friendRequest = new FriendRequest();

        $friendRequest->fromUserId = $fromUserId;
        $friendRequest->toUserId = $toUserId;

        $friendRequest->save();

        /** @var User $requestUser */
        $requestUser = User::where("id", $fromUserId)->first();

        $notification = new UserNotification();

        $notification->title   = "New friend request!";
        $notification->message = "You received a friend request from " . $requestUser->nickname;
        $notification->userId  = $toUserId;
        $notification->url     = "https://esac.gg/user/".$fromUserId;
        $notification->variant = "primary";

        $notification->save();

        return $friendRequest;
    }

    public function acceptRequest(Request $request)
    {
        $id = $request->input("id");

        /** @var FriendRequest $friendRequest */
        $friendRequest = FriendRequest::where("id", $id)->first();

        $friendRequest->statusId = FriendRequest::ACCEPTED;
        $friendRequest->save();

        /** @var User $requestUser */
        $requestUser = User::where("id", $friendRequest->toUserId)->first();

        $notification = new UserNotification();

        $notification->title   = "Friend request accepted!";
        $notification->message = $requestUser->nickname." has accepted your friend request.";
        $notification->userId  = $friendRequest->fromUserId;
        $notification->url     = "https://esac.gg/user/".$friendRequest->toUserId;
        $notification->variant = "primary";

        $notification->save();

        return $friendRequest;
    }

    public function rejectRequest(Request $request)
    {
        $id = $request->input("id");

        /** @var FriendRequest $friendRequest */
        $friendRequest = FriendRequest::where("id", $id)->first();

        $friendRequest->statusId = FriendRequest::REJECTED;
        $friendRequest->save();

        return $friendRequest;
    }
}
