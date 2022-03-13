<?php


namespace Modules\Play\Events\Listeners;


use Modules\Console\Events\MatchEnded;
use Modules\Play\Jobs\MatchStreamedAchievementVerification;

class DispatchMatchStreamedAchievementVerificationJob
{
    public function handle(MatchEnded $matchEnded)
    {
        $match = $matchEnded->match;

        MatchStreamedAchievementVerification::dispatch($match);
    }
}
