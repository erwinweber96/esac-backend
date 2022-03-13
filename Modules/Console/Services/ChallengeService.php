<?php


namespace Modules\Console\Services;


use Carbon\Carbon;
use Illuminate\Http\Response;
use Modules\Console\Exceptions\NoServerAvailable;
use Modules\Event\Entities\Participant;
use Modules\Group\Entities\Format;
use Modules\Group\Entities\Group;
use Modules\Map\Entities\MapPool;
use Modules\Map\Entities\MapPoolOrder;
use Modules\Map\ManiaExchange\Repositories\TMXMappackRepository;
use Modules\Match\Entities\MatchModel;
use Modules\User\Entities\ChallengeSettings;
use Modules\User\Entities\CoinTransaction;
use Modules\User\Entities\UserMessage;
use Modules\User\Entities\UserNotification;

/**
 * Class ChallengeService
 * @package Modules\Console\Services
 */
class ChallengeService
{
    const EVENT_ID = 2610;

    /** @var DedicatedControllerService $dedicatedControllerService */
    private $dedicatedControllerService;

    /**
     * ChallengeService constructor.
     * @param DedicatedControllerService $dedicatedControllerService
     */
    public function __construct(DedicatedControllerService $dedicatedControllerService)
    {
        $this->dedicatedControllerService = $dedicatedControllerService;
    }

    /**
     * @param UserMessage $challenge
     *
     * @return MatchModel
     *
     * @throws NoServerAvailable
     * @throws \Throwable
     */
    public function createMatch(UserMessage $challenge)
    {
        /** @var ChallengeSettings $challengeSettings */
        $challengeSettings = json_decode($challenge->message);

        $participants = Participant::where("event_id", self::EVENT_ID)->get();

        $players = [
            ["user_id" => $challenge->fromUserId],
            ["user_id" => $challenge->toUserId],
        ];

        $participantIds = [];
        foreach ($players as $player) {
            $participant = $participants->where('user_id', $player['user_id']);

            if (!$participant->count()) {
                $participant = new Participant();

                $participant->eventId = self::EVENT_ID;
                $participant->userId  = $player['user_id'];
                $participant->pending = false;
                $participant->type    = Participant::TYPE_USER;

                $participant->save();
            } else {
                /** @var Participant $participant */
                $participant = $participant->first();
            }

            $participantIds[] = $participant->id;
        }

        /** @var MapPool $mapPool */
        $mapPool = MapPool::where("mx_id", $challengeSettings->mappackId)
            ->where("event_id", self::EVENT_ID)
            ->first();

        if (!$mapPool) {
            $mapPool = new MapPool();

            $mapPool->mxId = $challengeSettings->mappackId;
            $mapPool->eventId = self::EVENT_ID;
            $mapPool->name = $challengeSettings->mappackId;

            $mapPool->save();
        }

        /** @var Format $format */
        $format = Format::where("event_id", self::EVENT_ID)->first();

        /** @var Group $group */
        $group = Group::where("event_id", self::EVENT_ID)->first();

        $match = new MatchModel();

        $match->name        = "Challenge " . $challenge->id;
        $match->date        = Carbon::now();
        $match->statusId    = MatchModel::STATUS_UPCOMING;
        $match->mapPoolId   = $mapPool->id;
        $match->groupId     = $group->id;

        $match->save();

        $match->formats()->sync([$format->id]);
        $match->participants()->sync($participantIds);

        //Select a random map
        /** @var TMXMappackRepository $mappackRepository */
        $mappackRepository = app(TMXMappackRepository::class);

        $tracks = $mappackRepository->getTracks($mapPool->mxId);

        foreach ($tracks as $track) {
            if ($track->getId() == $challengeSettings->mapId) {
                $selectedTrack = $track;
                break;
            }
        }

        if (!$selectedTrack) {
            throw new \Exception("Track not found in the selected mappack.");
        }

        foreach ($tracks as $track) {
            $mapPoolOrder = new MapPoolOrder();

            $mapPoolOrder->matchId   = $match->id;
            $mapPoolOrder->mapPoolId = $mapPool->id;
            $mapPoolOrder->order     = $track->getId() == $selectedTrack->getId() ? 1 : 0;
            $mapPoolOrder->mxMapId   = $track->getId();

            $mapPoolOrder->save();
        }

        //Start server
        $this->dedicatedControllerService->startMatch($match->id);

        //Send response
        return $match;
    }

    public function giveCoins(UserMessage $challenge)
    {
        /** @var ChallengeSettings $challengeSettings */
        $challengeSettings = json_decode($challenge->message);

        /** @var MatchModel $match */
        $match = MatchModel::where("id", $challengeSettings->matchId)->first();

        //give coins
        $player1 = $match->participants[0];
        $player2 = $match->participants[1];

        if (!$match->totalMatchResults[$player1->id]) {
            $match->totalMatchResults[$player1->id] = 0;
        }

        if (!$match->totalMatchResults[$player2->id]) {
            $match->totalMatchResults[$player2->id] = 0;
        }

        if ($match->totalMatchResults[$player1->id] == $match->totalMatchResults[$player2->id]) {
            // no coins given at cancelled match
            return;
        }

        if ($match->totalMatchResults[$player1->id] > $match->totalMatchResults[$player2->id]) {
            $player1->user->coins += $challengeSettings->coins;
            $player1->user->save();

            $coinTransaction = new CoinTransaction();
            $coinTransaction->userId      = $player1->id;
            $coinTransaction->amount      = $challengeSettings->coins;
            $coinTransaction->description = "Won challenge against ".$player2->user->nickname;
            $coinTransaction->save();

            $notification = new UserNotification();

            $notification->title   = "Challenge won!";
            $notification->message = "You won ".$challengeSettings->coins. " coins from ".$player2->user->nickname;
            $notification->userId  = $player1->userId;
            $notification->url     = "https://esac.gg/matches/".$challengeSettings->matchId;
            $notification->variant = "primary";

            $notification->save();
        } else {
            $player2->user->coins += $challengeSettings->coins;
            $player2->user->save();

            $coinTransaction = new CoinTransaction();
            $coinTransaction->userId      = $player2->id;
            $coinTransaction->amount      = $challengeSettings->coins;
            $coinTransaction->description = "Won challenge against ".$player1->user->nickname;
            $coinTransaction->save();

            $notification = new UserNotification();

            $notification->title   = "Challenge won!";
            $notification->message = "You won ".$challengeSettings->coins. " coins from ".$player1->user->nickname;
            $notification->userId  = $player2->userId;
            $notification->url     = "https://esac.gg/matches/".$challengeSettings->matchId;
            $notification->variant = "primary";

            $notification->save();
        }

        $challengeSettings->status = ChallengeSettings::ENDED;
        $challenge->message = json_encode($challengeSettings);
        $challenge->save();
    }

    public function rejectChallenge(UserMessage $challenge)
    {
        /** @var ChallengeSettings $challengeSettings */
        $challengeSettings = json_decode($challenge->message);
        $challengeSettings->status = ChallengeSettings::REJECTED;

        $challenge->message = json_decode($challengeSettings);
        $challenge->save();

        return $challenge;
    }
}
