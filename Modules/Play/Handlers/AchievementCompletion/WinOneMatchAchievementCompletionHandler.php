<?php

namespace Modules\Play\Handlers\AchievementCompletion;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Modules\Event\Entities\Participant;
use Modules\Match\Entities\MatchModel;
use Modules\User\Entities\User;

class WinOneMatchAchievementCompletionHandler extends AchievementCompletionHandler
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

        $wonMatches = 0;
        foreach ($legitEntries as $participant) {
            $matches = $participant
                ->matches
                ->where("status_id", MatchModel::STATUS_ENDED);

            foreach ($matches as $match) {
                if ($match->matchAlerts()->count()) {
                    continue;
                }

                $participantResult = $match->totalMatchResults[$participant->id]->result;

                $resultsOnly = [];
                foreach ($match->totalMatchResults as $totalMatchResult) {
                    if ($totalMatchResult == null) {
                        continue;
                    }

                    $resultsOnly[] = $totalMatchResult->result;
                }

                if (count($resultsOnly)) {
                    rsort($resultsOnly);

                    if ($match->participants->count() == 3 || $match->participants->count() == 4) {
                        //is in top 2
                        if ($resultsOnly[0] == $participantResult || $resultsOnly[1] == $participantResult) {
                            $wonMatches++;
                        }
                    }

                    if ($match->participants->count() == 2) {
                        if ($resultsOnly[0] == $participantResult) {
                            $wonMatches++;
                        }
                    }
                 }
            }
        }

        return $wonMatches;
    }
}
