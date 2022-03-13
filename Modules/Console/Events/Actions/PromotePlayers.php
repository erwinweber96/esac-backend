<?php


namespace Modules\Console\Events\Actions;


use Modules\Console\Events\MatchEnded;
use Modules\Event\Entities\Participant;
use Modules\Match\Entities\MatchModel;
use Modules\Match\Entities\MatchProperty;

/**
 * Class PromotePlayers
 * @package Modules\Console\Events\Actions
 */
class PromotePlayers
{
    public function handle(MatchEnded $trigger)
    {
        $match = $trigger->match;

        $promotesPlayers = $match
            ->properties
            ->where('key', MatchProperty::PROMOTION_MATCH_ID);

        if (!$promotesPlayers->count()) {
            return;
        }

        $promotesPlayers = array_values($promotesPlayers->toArray());

        //TODO: remake if more than 2 promote

        $player1 = null;
        $player2 = null;

        if (isset($match->participants[0])) {
            $player1 = $match->participants[0];
        }

        if (isset($match->participants[1])) {
            $player2 = $match->participants[1];
        }

        if ($player1) {
            $advancesToMatchId = $promotesPlayers[0]['value'];

            /** @var MatchModel $advancesToMatch */
            $advancesToMatch = MatchModel::where("id", $advancesToMatchId)->first();

            $participantIds = $advancesToMatch->participants->map(function (Participant  $participant) {
                return $participant->id;
            });

            $participantIds = $participantIds->toArray();
            $participantIds[] = $player1->id;

            $advancesToMatch->participants()->sync($participantIds);
        }

        if ($player2) {
            $advancesToMatchId = $promotesPlayers[1]['value'];

            /** @var MatchModel $advancesToMatch */
            $advancesToMatch = MatchModel::where("id", $advancesToMatchId)->first();

            $participantIds = $advancesToMatch->participants->map(function (Participant  $participant) {
                return $participant->id;
            });

            $participantIds = $participantIds->toArray();
            $participantIds[] = $player2->id;

            $advancesToMatch->participants()->sync($participantIds);
        }
    }
}
