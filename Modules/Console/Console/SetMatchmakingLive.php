<?php


namespace Modules\Console\Console;


use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Event\Entities\Event;
use Modules\Event\Entities\EventDate;
use Modules\Event\Entities\EventProperty;
use Modules\Event\Entities\Participant;
use Modules\Group\Entities\Group;
use Modules\Match\Entities\MatchModel;
use Modules\Match\Entities\MatchResult;
use Modules\Play\Jobs\CachePlayEventsJob;

class SetMatchmakingLive extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'matchmaking:live';

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

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Get matchmaking events with property and status = OPEN
        $eventProperties = EventProperty::where("key", EventProperty::MATCHMAKING_LADDER)->get();
        $eventIds = $eventProperties->map(function (EventProperty $eventProperty) {
            return $eventProperty->eventId;
        });

        // check if start_date > now() and set status = LIVE
        $events = Event::with(['dates'])
            ->whereIn("id", $eventIds)
            ->where("status_id", Event::STATUS_OPEN)
            ->get();

        /** @var Event $event */
        foreach ($events as $event) {
            $eventDates = $event->dates;

            /** @var EventDate $eventStartDate */
            $eventStartDate = $eventDates->where('name', EventDate::EVENT_START)->first();
            $eventStartDate = new Carbon($eventStartDate->date);

            if (Carbon::now()->gte($eventStartDate)) {
                // Update to ended
                $event->statusId = Event::STATUS_LIVE;
                $event->save();

                $participants = DB::table(Participant::TABLE_NAME)
                    ->where("event_id", $event->id)
                    ->get(["id"]);

                $participantIds = $participants->map(function ($participant) {
                    return $participant->id;
                });
                $participantIds = $participantIds->toArray();

                $matchResults = MatchResult::whereIn("participant_id", $participantIds)->get();
                $matchmakingResults = [];

                /** @var MatchResult $matchResult */
                foreach ($matchResults as $matchResult) {
                    if (!isset($matchmakingResults[$matchResult->participantId])) {
                        $matchmakingResults[$matchResult->participantId] = 0;
                    }

                    $matchmakingResults[$matchResult->participantId] += (int)$matchResult->result;
                }
            }
        }

        CachePlayEventsJob::dispatch();
    }
}
