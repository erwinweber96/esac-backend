<?php

namespace Modules\Play\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\DB;
use Modules\Event\Entities\Event;
use Modules\Event\Entities\EventDate;
use Modules\Event\Entities\EventProperty;
use Modules\Play\Jobs\CachePlayEventsJob;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class OpenRegistrations extends Command
{
    const COMMAND = "registrations:open";
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = self::COMMAND;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Opens registrations for events that are about to start.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $eventProperties = EventProperty::where("key", EventProperty::PLAY_ESAC_GG_EVENT)->get();
        $eventIds = $eventProperties->map(function (EventProperty $eventProperty) {
           return $eventProperty->eventId;
        });

        $openEvents = Event::where("status_id", Event::STATUS_OPEN)
            ->whereIn("id", $eventIds)
            ->get();

        if (!$openEvents) {
            return;
        }

        /** @var Event $event */
        foreach ($openEvents as $event) {
            if ($event->registrationOpen) {
                continue;
            }

            /** @var EventDate $registrationOpens */
            $registrationOpens = $event->dates->firstWhere('name', EventDate::REGISTRATION_OPEN);

            if (!$registrationOpens) {
                continue;
            }

            $registrationOpens = new Carbon($registrationOpens->date);

            if ($registrationOpens->lte(Carbon::now())) {
                $event->registrationOpen = true;
                $event->save();
            }
        }

        CachePlayEventsJob::dispatch();
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [];
    }
}
