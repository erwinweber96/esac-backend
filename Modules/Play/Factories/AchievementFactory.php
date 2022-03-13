<?php

namespace Modules\Play\Factories;

use Modules\Play\Entities\PlayFiveMatchesOnRandomLadder;
use Modules\Play\Entities\PlayThreeMatchesAchievement;
use Modules\Play\Entities\StreamOneMatchAchievement;
use Modules\Play\Entities\StreamWhileWinningAchievement;
use Modules\Play\Entities\WinOneMatchAchievement;
use Modules\Play\Handlers\AchievementCompletion\PlayFiveMatchesOnRandomLadderCompletionHandler;

class AchievementFactory
{
    public function getAchievementById($achievementId)
    {
        switch($achievementId) {
            case WinOneMatchAchievement::getId():
                return new WinOneMatchAchievement();
            case PlayThreeMatchesAchievement::getId():
                return new PlayThreeMatchesAchievement();
            case StreamOneMatchAchievement::getId():
                return new StreamOneMatchAchievement();
            case StreamWhileWinningAchievement::getId():
                return new StreamWhileWinningAchievement();
            case PlayFiveMatchesOnRandomLadder::getId():
                return new PlayFiveMatchesOnRandomLadderCompletionHandler();
        }
    }
}
