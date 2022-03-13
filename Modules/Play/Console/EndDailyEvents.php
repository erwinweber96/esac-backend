<?php


namespace Modules\Play\Console;


use Illuminate\Console\Command;
use Modules\Event\Entities\Event;
use Modules\Event\Entities\EventProperty;
use Modules\Event\Entities\Participant;
use Modules\Group\Entities\Group;
use Modules\Match\Entities\MatchModel;
use Modules\Match\Entities\MatchResult;
use Modules\Play\Jobs\CachePlayEventsJob;
use Modules\User\Entities\CoinTransaction;

class EndDailyEvents extends Command
{
    const COMMAND = "daily_events:end";

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = self::COMMAND;

    public function handle()
    {
        //get open matches
        $eventProperties = EventProperty::where("key", EventProperty::PLAY_ESAC_GG_EVENT)->get();
        $eventIds = $eventProperties->map(function (EventProperty $eventProperty) {
            return $eventProperty->eventId;
        });

        $liveEvents = Event::where("status_id", Event::STATUS_LIVE)
            ->whereIn("id", $eventIds)
            ->get();

        if (!$liveEvents->count()) {
            return;
        }

        /** @var Event $liveEvent */
        foreach ($liveEvents as $liveEvent) {
            $hasEnded = true;

            /** @var Group $group */
            foreach ($liveEvent->groups as $group) {
                foreach ($group->matches as $match) {
                    if ($match->statusId != MatchModel::STATUS_ENDED) {
                        $hasEnded = false;
                    }
                }
            }

            if ($hasEnded) {
                $liveEvent->statusId = Event::STATUS_ENDED;
                $liveEvent->save();

                $final = $liveEvent->groups[2]->matches[0];
                $this->giveOutCoins($final);
            }
        }

        CachePlayEventsJob::dispatch();
    }

    private function getMatchWinners(MatchModel $match)
    {
        $results = collect($match->totalMatchResults);
        $results = $results->sortByDesc('result', SORT_NATURAL);
        $winners = $results->take(2);
        $winners = $winners->map(function (?MatchResult $result, $participantId) {
            return $participantId;
        });
        $arrayWithObject = array_values((array)$winners);
        return array_values((array)$arrayWithObject[0]);
    }

    private function giveOutCoins(MatchModel $final)
    {
        $winners = $this->getMatchWinners($final);
        $winner1ParticipantId = $winners[0];
        $winner2ParticipantId = $winners[1];

        /** @var Participant $winner1Participant */
        $winner1Participant = Participant::where('id', $winner1ParticipantId)->first();

        /** @var Participant $winner2Participant */
        $winner2Participant = Participant::where("id", $winner2ParticipantId)->first();

        $coinTransaction = new CoinTransaction();
        $coinTransaction->userId      = $winner1Participant->user->id;
        $coinTransaction->amount      = 100;
        $coinTransaction->description = "Daily Tournament: 1st place";
        $coinTransaction->save();

        $winner1Participant->user->coins += 100;
        $winner1Participant->save();

        $coinTransaction = new CoinTransaction();
        $coinTransaction->userId      = $winner2Participant->user->id;
        $coinTransaction->amount      = 50;
        $coinTransaction->description = "Daily Tournament: 2nd place";
        $coinTransaction->save();

        $winner2Participant->user->coins += 50;
        $winner2Participant->save();
    }
}
