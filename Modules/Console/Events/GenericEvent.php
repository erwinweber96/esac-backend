<?php

namespace Modules\Console\Events;


use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Modules\Console\Entities\GenericEventData;

/**
 * Class GenericEvent
 * @package Module\Console\Events
 */
class GenericEvent implements ShouldBroadcastNow
{
    /** @var GenericEventData */
    public $event;

    /**
     * GenericEvent constructor.
     * @param GenericEventData $event
     */
    public function __construct(GenericEventData $event)
    {
        $this->event = $event;
    }

    public function broadcastOn()
    {
        return new Channel($this->event->getChannel());
    }

    public function broadcastAs()
    {
        return $this->event->getName();
    }
}
