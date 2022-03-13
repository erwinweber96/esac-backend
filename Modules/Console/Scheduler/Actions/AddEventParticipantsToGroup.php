<?php


namespace Modules\Console\Scheduler\Actions;


use Modules\Event\Entities\Participant;

class AddEventParticipantsToGroup extends ScheduledActionHandler
{
    public int $eventId;

    public function run()
    {
        $participants = Participant::query();
        $participants = $this->applyFilters($participants);

        //TODO: no idea what i'm doing
    }
}
