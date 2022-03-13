<?php


namespace Modules\Play\Handlers\AchievementCompletion;


use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Modules\Play\Entities\PlayerMatchStream;
use Modules\User\Entities\User;

class StreamOneMatchAchievementCompletionHandler extends AchievementCompletionHandler
{
    public function handle(): int
    {
        /** @var User $user */
        $user = Auth::user();

        return PlayerMatchStream::where("user_id", $user->id)
            ->whereDate("created_at", Carbon::today())
            ->count();
    }
}
