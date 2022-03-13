<?php


namespace Modules\Event\Events;


use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Modules\Event\Entities\Event;

class EventCreated
{
    /** @var Event $event */
    public $event;

    /**
     * EventDeleted constructor.
     * @param Event $event
     */
    public function __construct($event)
    {
        $this->event = $event;
    }
}
