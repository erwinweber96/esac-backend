<?php


namespace Modules\Console\Events\Actions;


use Modules\Console\Events\MatchEnded;
use Modules\Event\Entities\Participant;
use Modules\Match\Entities\MatchModel;
use Modules\Match\Entities\MatchProperty;

/**
 * Class DemotePlayers
 * @package Modules\Console\Events\Actions
 */
class DemotePlayers
{
    public function handle(MatchEnded $trigger)
    {
        $match = $trigger->match;

        $demotesPlayers = $match
            ->properties
            ->where('key', MatchProperty::DEMOTION_MATCH_ID);

        if (!$demotesPlayers->count()) {
            return;
        }

        $demotesPlayers = array_values($demotesPlayers->toArray());

        //TODO: remake if more than 2 demote

        $player3 = null;
        $player4 = null;

        if (isset($match->participants[2])) {
            $player3 = $match->participants[2];
        }

        if (isset($match->participants[3])) {
            $player4 = $match->participants[3];
        }

        if ($player3) {
            $advancesToMatchId = $demotesPlayers[0]['value'];

            /** @var MatchModel $advancesToMatch */
            $advancesToMatch = MatchModel::where("id", $advancesToMatchId)->first();

            $participantIds = $advancesToMatch->participants->map(function (Participant  $participant) {
                return $participant->id;
            });

            $participantIds = $participantIds->toArray();
            $participantIds[] = $player3->id;

            $advancesToMatch->participants()->sync($participantIds);
        }

        if ($player4) {
            $advancesToMatchId = $demotesPlayers[1]['value'];

            /** @var MatchModel $advancesToMatch */
            $advancesToMatch = MatchModel::where("id", $advancesToMatchId)->first();

            $participantIds = $advancesToMatch->participants->map(function (Participant  $participant) {
                return $participant->id;
            });

            $participantIds = $participantIds->toArray();
            $participantIds[] = $player4->id;

            $advancesToMatch->participants()->sync($participantIds);
        }
    }
}
