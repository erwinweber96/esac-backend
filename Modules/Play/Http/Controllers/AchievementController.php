<?php


namespace Modules\Play\Http\Controllers;


use App\Models\User;
use Carbon\Carbon;
use Modules\Event\Entities\EventProperty;
use Modules\Play\Entities\Achievement;
use Modules\Play\Entities\AchievementRedeem;
use Modules\Play\Entities\CaseDrop;
use Modules\Play\Entities\PlayThreeMatchesAchievement;
use Modules\Play\Entities\StreamOneMatchAchievement;
use Modules\Play\Factories\AchievementCompletionHandlerFactory;
use Modules\Play\Factories\AchievementFactory;
use Modules\Play\Handlers\AchievementCompletion\AchievementCompletionHandler;
use Modules\User\Entities\CoinTransaction;
use Symfony\Component\HttpFoundation\Response;

class AchievementController
{
    private AchievementCompletionHandlerFactory $achievementCompletionHandlerFactory;

    private AchievementFactory $achievementFactory;

    /**
     * @param AchievementCompletionHandlerFactory $achievementCompletionHandlerFactory
     * @param AchievementFactory $achievementFactory
     */
    public function __construct(AchievementCompletionHandlerFactory $achievementCompletionHandlerFactory, AchievementFactory $achievementFactory)
    {
        $this->achievementCompletionHandlerFactory = $achievementCompletionHandlerFactory;
        $this->achievementFactory = $achievementFactory;
    }

    public function getAchievementProperties()
    {
        /** @var EventProperty $badgeId1131Achievement */
        $badgeId1131Achievement = EventProperty::where("key", EventProperty::BADGE_ID_1131_ACHIEVEMENT)
            ->orderBy("id", "desc")
            ->first();

        if (!$badgeId1131Achievement) {
            return [];
        }

        return [
            $badgeId1131Achievement
        ];
    }

    public function getCompletions()
    {
        $achievements = [1,2,3,4,5];

        $completions = [];
        foreach($achievements as $achievementId) {
            $completions[$achievementId] = $this->getCompletion($achievementId);
        }

        return $completions;
    }

    public function getCompletion($achievementId)
    {
        /** @var AchievementCompletionHandler $handler */
        $handler = app($this->achievementCompletionHandlerFactory->getHandler($achievementId));

        return $handler->handle();
    }

    public function redeem($achievementId)
    {
        /** @var AchievementCompletionHandler $handler */
        $handler = app($this->achievementCompletionHandlerFactory->getHandler($achievementId));

        /** @var Achievement $achievement */
        $achievement = $this->achievementFactory->getAchievementById($achievementId);

        $multiplier = 1;
        $completion = $handler->handle();
        if ($completion >= $achievement::getDefaultTarget()) {
            /** @var \Modules\User\Entities\User $user */
            $user = auth()->user();

            //checks badge abilities
            switch ($user->badgeId) {
                case 1128:
                    if ($achievement instanceof StreamOneMatchAchievement) {
                        if (!$completion >= 3) {
                            return response()->json([
                                "error" => [
                                    "message" => "You have not yet completed the achievement."
                                ]
                            ], Response::HTTP_BAD_REQUEST);
                        }

                        $multiplier = 3;
                    }
                    break;
                case 1129:
                    if ($achievement instanceof PlayThreeMatchesAchievement) {
                        if (!$completion >= 5) {
                            return response()->json([
                                "error" => [
                                    "message" => "You have not yet completed the achievement."
                                ]
                            ], Response::HTTP_BAD_REQUEST);
                        }

                        $multiplier = 3;
                    }
                    break;
            }

            $redeemed = AchievementRedeem::where("user_id", $user->id)
                ->whereDate("created_at", Carbon::today())
                ->get();

            if ($redeemed->count()) {
                return response()->json([
                    "error" => [
                        "message" => "You have already redeemed this achievement."
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }

            $redeem = new AchievementRedeem();
            $redeem->userId = $user->id;
            $redeem->achievementId = $achievementId;
            $redeem->save();

            $user->coins += ($achievement::getReward() * $multiplier);

            $coinTransaction = new CoinTransaction();

            $coinTransaction->userId = $user->id;
            $coinTransaction->amount = ($achievement::getReward() * $multiplier);
            $coinTransaction->description = "Achievement reward";

            $coinTransaction->save();

            return response()->json(["message" => "Success."], Response::HTTP_OK);
        }

        return response()->json([
            "error" => [
                "message" => "You have not yet completed the achievement."
            ]
        ], Response::HTTP_BAD_REQUEST);
    }
}
