<?php


namespace Modules\Event\Builders;


use App\Model\Builder;
use Modules\Event\Entities\Event;
use Modules\Event\Entities\Participant;

class ParticipantBuilder implements Builder
{
    /** @var array */
    private $participant;

    public function prepare(): Builder
    {
        $this->participant = [];
        return $this;
    }

    public function build()
    {
        return $this->participant;
    }

    public function setEvent(Event $event): self
    {
        $this->participant['event_id'] = $event->id;
        $this->participant['type'] = $event->isTeamEvent ?
            Participant::TYPE_TEAM : Participant::TYPE_USER;
        return $this;
    }

    public function setParticipantId(int $participantId): self
    {
        if ($this->participant['type'] == Participant::TYPE_TEAM) {
            $this->participant['page_id'] = $participantId;
            return $this;
        }

        $this->participant['user_id'] = $participantId;
        return $this;
    }

    public function setPending(bool $pending): self
    {
        $this->participant['pending'] = $pending;
        return $this;
    }
}
