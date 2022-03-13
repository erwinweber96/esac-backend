<?php


namespace Modules\Console\Console;


use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Console\Api\DedicatedControllerApi;
use Modules\Event\Entities\Event;
use Modules\Event\Entities\EventDate;
use Modules\Event\Entities\EventProperty;
use Modules\Event\Entities\Participant;
use Modules\Group\Entities\Group;
use Modules\Match\Entities\MatchModel;
use Modules\User\Entities\CoinTransaction;
use Nwidart\Modules\Collection;


class GiveMatchmakingCoins extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'matchmaking:coins';

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
        // Matchmaking Ladder Property
        $eventProperties = EventProperty::where("key", EventProperty::MATCHMAKING_LADDER)->get();
        $eventIds = $eventProperties->map(function (EventProperty $eventProperty) {
            return $eventProperty->eventId;
        });

        // Get events with those properties and status LIVE
        $events = Event::with(['dates'])
            ->whereIn("id", $eventIds)
            ->where("status_id", Event::STATUS_LIVE)
            ->get();

        // Check if now > end_date
        /** @var Event $event */
        foreach ($events as $event) {
            $eventDates = $event->dates;

            /** @var EventDate $eventEndDate */
            $eventEndDate = $eventDates->where('name', EventDate::EVENT_END)->first();
            $eventEndDate = new Carbon($eventEndDate->date);

            if (Carbon::now()->gte($eventEndDate)) {
                $liveMatches = DB::table(MatchModel::TABLE_NAME)
                    ->where("status_id", MatchModel::STATUS_LIVE)
                    ->where("group_id", $event->groups->first()->id)
                    ->get();

                if ($liveMatches->count()) {
                    continue;
                }

                // Update to ended
                $event->statusId = Event::STATUS_ENDED;
                $event->save();

                // Get top 4 and give coins
                $group = DB::table(Group::TABLE_NAME)->where("event_id", $event->id)->first(["id"]);
                $groupId = $group->id;

                $matches = MatchModel::where("group_id", $groupId)->get();
                $matchmakingResults = [];

                /** @var MatchModel $match */
                foreach($matches as $match) {
                    foreach ($match->totalMatchResults as $participantId => $matchResult) {
                        if (!isset($matchmakingResults[$participantId])) {
                            $matchmakingResults[$participantId] = 0;
                        }

                        if (!$matchResult) {
                            $matchResult['result'] = 0;
                        }

                        $matchmakingResults[$participantId] += $matchResult['result'];
                    }
                }

                asort($matchmakingResults);
                $matchmakingResults = array_reverse($matchmakingResults, true);

                $prizePool = [1000, 500, 250, 100];
                $prized = 0;
                foreach ($matchmakingResults as $participantId => $matchmakingResult) {
                    if ($prized == 4) {
                        break;
                    }

                    /** @var Participant $participant */
                    $participant = Participant::where("id", $participantId)->first();

                    $participant->user->coins += $prizePool[$prized];
                    $participant->user->save();

                    $coinTransaction = new CoinTransaction();

                    $coinTransaction->userId = $participant->userId;
                    $coinTransaction->description = "Matchmaking Ladder Position: " . ($prized + 1);
                    $coinTransaction->amount = $prizePool[$prized];

                    $coinTransaction->save();

                    $prized++;
                }
            }
        }
    }
}
