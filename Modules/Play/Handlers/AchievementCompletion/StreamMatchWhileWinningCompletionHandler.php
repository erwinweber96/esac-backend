<?php


namespace Modules\Play\Handlers\AchievementCompletion;


use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Modules\Play\Entities\PlayerMatchStream;
use Modules\Play\Entities\StreamWhileWinningAchievement;
use Modules\User\Entities\User;

class StreamMatchWhileWinningCompletionHandler extends AchievementCompletionHandler
{
    public function handle(): int
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->badgeId != StreamWhileWinningAchievement::BADGE_ID) {
            return 0;
        }

        return PlayerMatchStream::where("user_id", $user->id)
            ->where("has_won", true)
            ->whereDate("created_at", Carbon::today())
            ->count();
    }
}
