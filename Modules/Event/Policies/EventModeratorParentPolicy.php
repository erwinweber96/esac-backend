<?php


namespace Modules\Event\Policies;


use Modules\Event\Entities\Event;
use Modules\Event\Entities\EventModerator;
use Modules\User\Entities\User;

class EventModeratorParentPolicy
{
    /** @var EventModerator $member */
    protected $moderator;

    public function before(User $user, $ability, Event $event)
    {
        $moderator = EventModerator::where("user_id", $user->id)
            ->where("event_id", $event->id)
            ->first();

        if (!$moderator) {
            return false;
        }

        $this->moderator = $moderator;
    }
}
