<?php


namespace Modules\User\Events;


use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Modules\User\Entities\UserNotification;

class NewUserNotificationCreated implements ShouldBroadcastNow
{
    /** @var UserNotification $notification */
    public $notification;

    /**
     * NewUserNotificationCreated constructor.
     * @param UserNotification $notification
     */
    public function __construct(UserNotification $notification)
    {
        $this->notification = $notification;
    }

    public function broadcastOn()
    {
        return new Channel("user_".$this->notification->userId);
    }

    public function broadcastAs()
    {
        return "new_notification";
    }
}
