<?php


namespace Modules\Event\Jobs;


use Modules\Event\Builders\ParticipantBuilder;
use Modules\Event\Entities\Event;
use Modules\Event\Entities\EventProperty;
use Modules\Event\Http\Requests\RegisterParticipantRequest;

class BuildParticipant
{
    /** @var ParticipantBuilder $participantBuilder */
    private $participantBuilder;

    /**
     * BuildParticipant constructor.
     * @param ParticipantBuilder $participantBuilder
     */
    public function __construct(ParticipantBuilder $participantBuilder)
    {
        $this->participantBuilder = $participantBuilder->prepare();
    }

    /**
     * @param RegisterParticipantRequest $request
     * @param Event $event
     * @return ParticipantBuilder
     */
    public function execute(RegisterParticipantRequest $request, Event $event)
    {
        return $this->participantBuilder
            ->setEvent($event)
            ->setParticipantId($request->input("participantId"))
            ->setPending($this->isPending($event));
    }

    /**
     * @param Event $event
     * @return bool
     */
    private function isPending(Event $event): bool
    {
        $isFranceBased = $event->properties->where("key", EventProperty::FRANCE_BASED)->count();

        if ($isFranceBased) {
            return true;
        }

        $requiresPending = $event->properties->where("key", EventProperty::PENDING_REGISTRATION);

        if ($requiresPending->count()) {
            return $requiresPending->first()->value == "1";
        }

        return false;
    }
}
