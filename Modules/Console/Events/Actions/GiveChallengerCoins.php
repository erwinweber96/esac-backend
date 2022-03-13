<?php


namespace Modules\Console\Events\Actions;


use Modules\Console\Events\MatchEnded;
use Modules\Console\Services\ChallengeService;
use Modules\User\Entities\UserMessage;

/**
 * Class GiveChallengerCoins
 * @package Modules\Console\Events\Actions
 */
class GiveChallengerCoins
{
    public function handle(MatchEnded $trigger)
    {
        if ($trigger->match->group->eventId != ChallengeService::EVENT_ID) {
            return;
        }

        /** @var ChallengeService $challengeService */
        $challengeService = app(ChallengeService::class);

        $matchNameSplit = explode(" ", $trigger->match->name);
        $challengeId    = $matchNameSplit[1];

        /** @var UserMessage $challenge */
        $challenge = UserMessage::where("id", $challengeId)->first();

        $challengeService->giveCoins($challenge);
    }
}
