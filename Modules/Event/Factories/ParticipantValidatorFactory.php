<?php


namespace Modules\Event\Factories;


use Modules\Event\Entities\Event;
use Modules\Event\Entities\Participant;
use Modules\Event\Validators\ParticipantValidator;
use Modules\Event\Validators\PlayerValidator;
use Modules\Event\Validators\TeamValidator;

class ParticipantValidatorFactory
{
    /**
     * @param Event $event
     * @return ParticipantValidator
     */
    public function make(Event $event)
    {
        if ($event->isTeamEvent) {
            return app(TeamValidator::class);
        }

        return app(PlayerValidator::class);
    }
}
