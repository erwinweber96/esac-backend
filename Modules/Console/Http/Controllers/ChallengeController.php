<?php


namespace Modules\Console\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Console\Exceptions\NoServerAvailable;
use Modules\Console\Services\ChallengeService;
use Modules\User\Entities\ChallengeSettings;
use Modules\User\Entities\User;
use Modules\User\Entities\UserMessage;

/**
 * Class ChallengeController
 * @package Modules\Console\Http\Controllers
 */
class ChallengeController
{
    /** @var ChallengeService $service */
    private $service;

    /**
     * ChallengeController constructor.
     * @param ChallengeService $service
     */
    public function __construct(ChallengeService $service)
    {
        $this->service = $service;
    }

    public function acceptChallenge($challengeId)
    {
        /** @var User $user */
        $user = auth()->user();

        /** @var UserMessage $challenge */
        $challenge = UserMessage::where("id", $challengeId)->first();

        /** @var ChallengeSettings $challengeSettings */
        $challengeSettings = json_decode($challenge->message);

        if ($user->coins < $challengeSettings->coins) {
            return response()->json(["errors" => [
                "message" => "Not enough coins."
            ]], Response::HTTP_BAD_REQUEST);
        }

        if ($challengeSettings->status != ChallengeSettings::PENDING) {
            return response()->json(["errors" => [
                "message" => "Challenge is ongoing or has ended."
            ]], Response::HTTP_BAD_REQUEST);
        }

        try {
            $match = $this->service->createMatch($challenge);
        } catch (NoServerAvailable $exception) {
            return response()->json(["errors" => [
                "message" => "No server available. Could not start challenge."
            ]], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Throwable $exception) {
            return response()->json(["errors" => [
                "message" => "Could not start challenge."
            ]], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        /** @var ChallengeSettings $challengeSettings */
        $challengeSettings = json_decode($challenge->message);

        $challengeSettings->matchId = $match->id;
        $challengeSettings->status = ChallengeSettings::LIVE;
        $challenge->message = json_encode($challengeSettings);
        $challenge->save();

        $userMessage = new UserMessage();

        $userMessage->toUserId = $challenge->toUserId;
        $userMessage->fromUserId = $challenge->fromUserId;
        $userMessage->message = $match;
        $userMessage->type = UserMessage::TYPE_MATCH;
        $userMessage->channel = $challenge->channel;

        $userMessage->save();

        return $userMessage;
    }
}
