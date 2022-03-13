<?php


namespace Modules\Event\Console;


use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Modules\Event\Http\Controllers\EventController;

class CacheFeaturedEvents extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'featured_events:cache';

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

        $events = $eventController->getFeaturedEvents();

        return Storage::disk("do_spaces")
            ->put("/cache/featured_events.json", json_encode($events), "public");
    }
}
