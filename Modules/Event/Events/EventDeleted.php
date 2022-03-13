<?php


namespace Modules\Event\Events;


use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Modules\Event\Entities\Event;

class EventDeleted
{
    /** @var Event $event */
    public $event;

    /**
     * EventDeleted constructor.
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }
}
