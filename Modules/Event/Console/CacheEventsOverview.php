<?php


namespace Modules\Event\Console;


use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Modules\Event\Entities\Event;
use Modules\Event\Http\Controllers\EventController;

/**
 * Class CacheEvents
 * @package Modules\Event\Console
 */
class CacheEventsOverview extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'events:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        /** @var EventController $eventController */
        $eventController = app(EventController::class);

        $events = $eventController->getEvents();

        return Storage::disk("do_spaces")
            ->put("/cache/events.json", json_encode($events), "public");
    }
}
