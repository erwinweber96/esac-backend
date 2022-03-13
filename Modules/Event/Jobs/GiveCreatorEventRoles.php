<?php


namespace Modules\Event\Jobs;


use Modules\Event\Entities\Event;
use Modules\Event\Entities\EventModerator;
use Modules\Event\Entities\EventModeratorRole;
use Modules\User\Entities\User;

class GiveCreatorEventRoles
{
    public function execute(Event $event)
    {
        /** @var User $creator */
        $creator = auth()->user();

        $eventModerator = new EventModerator();

        $eventModerator->userId = $creator->id;
        $eventModerator->eventId = $event->id;

        $eventModerator->save();

        foreach (EventModeratorRole::ROLES as $role) {
            $eventModeratorRole = new EventModeratorRole();

            $eventModeratorRole->name        = $role;
            $eventModeratorRole->eventId     = $event->id;
            $eventModeratorRole->moderatorId = $eventModerator->id;

            $eventModeratorRole->save();
        }
    }
}
