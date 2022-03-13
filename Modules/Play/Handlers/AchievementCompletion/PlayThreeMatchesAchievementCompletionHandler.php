<?php


namespace Modules\Play\Handlers\AchievementCompletion;


use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Modules\Event\Entities\Event;
use Modules\Event\Entities\EventProperty;
use Modules\Event\Entities\Participant;
use Modules\Match\Entities\MatchModel;
use Modules\Play\Entities\PlayThreeMatchesAchievement;
use Modules\Play\Services\HourlyEventService;
use Modules\User\Entities\User;

class PlayThreeMatchesAchievementCompletionHandler extends AchievementCompletionHandler
{
    public function handle(): int
    {
        /** @var User $user */
        $user = Auth::user();

        $participantEntries = Participant::where("user_id", $user->id)
            ->whereDate("created_at", Carbon::today())
            ->get();

        $legitEntries = [];

        /** @var Participant $participantEntry */
        foreach ($participantEntries as $participantEntry) {
            if ($this->isHourlyShowdown($participantEntry->event)) {
                $legitEntries[] = $participantEntry;
            }

            if ($this->isMatchmakingLadder($participantEntry->event)) {
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
