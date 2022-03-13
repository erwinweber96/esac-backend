<?php


namespace Modules\Play\Factories;


use Modules\Play\Entities\PlayFiveMatchesOnRandomLadder;
use Modules\Play\Entities\StreamOneMatchAchievement;
use Modules\Play\Entities\StreamWhileWinningAchievement;
use Modules\Play\Entities\WinOneMatchAchievement;
use Modules\Play\Entities\PlayThreeMatchesAchievement;
use Modules\Play\Handlers\AchievementCompletion\PlayFiveMatchesOnRandomLadderCompletionHandler;
use Modules\Play\Handlers\AchievementCompletion\PlayThreeMatchesAchievementCompletionHandler;
use Modules\Play\Handlers\AchievementCompletion\StreamMatchWhileWinningCompletionHandler;
use Modules\Play\Handlers\AchievementCompletion\StreamOneMatchAchievementCompletionHandler;
use Modules\Play\Handlers\AchievementCompletion\WinOneMatchAchievementCompletionHandler;

class AchievementCompletionHandlerFactory
{
    public function getHandler($achievementId): string
    {
        switch($achievementId) {
            case WinOneMatchAchievement::getId():
                return WinOneMatchAchievementCompletionHandler::class;
            case PlayThreeMatchesAchievement::getId():
                return PlayThreeMatchesAchievementCompletionHandler::class;
            case StreamOneMatchAchievement::getId():
                return StreamOneMatchAchievementCompletionHandler::class;
            case StreamWhileWinningAchievement::getId():
                return StreamMatchWhileWinningCompletionHandler::class;
            case PlayFiveMatchesOnRandomLadder::getId():
                return PlayFiveMatchesOnRandomLadderCompletionHandler::class;
        }
    }
}
