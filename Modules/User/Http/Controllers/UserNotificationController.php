<?php


namespace Modules\User\Http\Controllers;


use Modules\User\Entities\User;
use Modules\User\Entities\UserNotification;

class UserNotificationController
{
    public function get()
    {
        /** @var User $user */
        $user = auth()->user();

        return UserNotification::where("user_id", $user->id)
            ->orderBy("created_at", "desc")
            ->get();
    }

    public function markNotificationsRead()
    {
        /** @var User $user */
        $user = auth()->user();

        return UserNotification::where("user_id", $user->id)
            ->update([
                "read" => true
            ]);
    }
}
