<?php

namespace Modules\Play\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Modules\Play\Http\Controllers\PlayController;

class CachePlayEventsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        /** @var PlayController $eventController */
        $eventController = app(PlayController::class);

        $events = $eventController->getEvents();

        return Storage::disk("do_spaces")
            ->put("/cache/play/events.json", json_encode($events), "public");
    }
}
