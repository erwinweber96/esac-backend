<?php


namespace Modules\Play\Handlers\AchievementCompletion;


use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Modules\Event\Entities\EventProperty;
use Modules\Event\Entities\Participant;
use Modules\Match\Entities\MatchModel;
use Modules\Play\Entities\PlayFiveMatchesOnRandomLadder;
use Modules\User\Entities\User;

class PlayFiveMatchesOnRandomLadderCompletionHandler extends AchievementCompletionHandler
{
    public function handle(): int
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->badgeId != PlayFiveMatchesOnRandomLadder::BADGE_ID) {
            return 0;
        }

        $participantEntries = Participant::where("user_id", $user->id)
            ->whereDate("created_at", Carbon::today())
            ->get();

        /** @var EventProperty $randomEvent */
        $randomEvent = EventProperty::where("key", EventProperty::BADGE_ID_1131_ACHIEVEMENT)
            ->whereDate("created_at", Carbon::today())
            ->first();

        $legitEntries = [];

        /** @var Participant $participantEntry */
        foreach ($participantEntries as $participantEntry) {
            if ($participantEntry->eventId == $randomEvent->eventId) {
                $legitEntries[] = $participantEntry;
            }
        }

        if (!count($legitEntries)) {
            return 0;
        }

        $matchesPlayed = 0;
        foreach ($legitEntries as $participant) {
            $matches = $participant
                ->matches
                ->where("status_id", MatchModel::STATUS_ENDED);

            foreach ($matches as $match) {
                if ($match->matchAlerts()->count()) {
                    continue;
                }

                $matchesPlayed++;
            }
        }

        return $matchesPlayed;
    }
}
