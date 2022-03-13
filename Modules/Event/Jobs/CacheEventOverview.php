<?php

namespace Modules\Event\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Modules\Event\Entities\Event;
use Modules\Event\Http\Controllers\EventV2Controller;

class CacheEventOverview implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Event $event;

    /**
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    public function handle()
    {
        /** @var EventV2Controller $controller */
        $controller = app(EventV2Controller::class);

        $data = $controller->getEventOverview($this->event->slug);

        return Storage::disk("do_spaces")
            ->put("/cache/events/overview/".$this->event->slug.".json", json_encode($data), "public");
    }
}
