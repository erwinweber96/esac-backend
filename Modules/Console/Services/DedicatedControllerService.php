<?php


namespace Modules\Console\Services;


use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Modules\Console\Api\DedicatedControllerApi;
use Modules\Console\Entities\DedicatedController;
use Modules\Console\Exceptions\NoServerAvailable;
use Modules\Event\Entities\Event;
use Modules\Event\Entities\EventProperty;
use Modules\Event\Entities\Participant;
use Modules\Group\Entities\Format;
use Modules\Group\Entities\FormatType;
use Modules\Group\Entities\MatchSetting;
use Modules\Map\Entities\MapPool;
use Modules\Map\Entities\MapPoolOrder;
use Modules\Map\ManiaExchange\Entities\Track;
use Modules\Match\Entities\MatchModel;
use Modules\Match\Entities\MatchAlert;
use Modules\Match\Jobs\SendMatchStatusUpdatedEventToDiscordWebhook;
use Modules\Match\Repositories\MatchRepository;
use Modules\Page\Entities\TeamEloHistory;
use Modules\Play\Entities\EloHistory;
use Modules\Play\Services\RankingService;

/**
 * Class DedicatedControllerService
 * @package Modules\Console\Services
 */
class DedicatedControllerService
{
    /** @var DedicatedControllerApi $api */
    private $api;

    /** @var RankingService $rankingService */
    private $rankingService;

    /**
     * DedicatedControllerService constructor.
     * @param DedicatedControllerApi $api
     * @param RankingService $rankingService
     */
    public function __construct(DedicatedControllerApi $api, RankingService $rankingService)
    {
        $this->api = $api;
        $this->rankingService = $rankingService;
    }

    /**
     * @param $matchId
     * @throws NoServerAvailable
     * @throws \Throwable
     */
    public function startMatch($matchId)
    {
        /** @var MatchModel $match */
        $match = MatchModel::where("id", $matchId)
            ->with("mapPool")
            ->with("formats")
            ->first();

        try {
            $dedicated = $this->reserve($matchId);
        } catch (NoServerAvailable $exception) {
            $matchAlert = new MatchAlert();

            $matchAlert->matchId = $matchId;
            $matchAlert->message = "Match cancelled: No game server available.";
            $matchAlert->type    = "danger";
            $matchAlert->public  = true;

            $matchAlert->save();

            $match->statusId = MatchModel::STATUS_ENDED;
            $match->save();

            throw $exception;
        }

        $port       = $dedicated->port;

        /** @var MapPool $mapPool */
        $mapPool = $match->mapPool()->first();
        $tracks  = collect($mapPool->mxData->getTracks());
        $maps    = $tracks->map(function($track) {
            return ["id" => $track['id']];
        });

        /** @var MapPoolOrder[]|Collection $orders */
        $orders = MapPoolOrder::where("match_id", $matchId)
            ->orderBy("order", "asc")
            ->where("order", "!=", 0)
            ->get();

        $sortedMaps = collect();
        foreach ($orders as $order) {
            $sortedMaps->add($maps->where("id", $order->mxMapId)->first());
        }

        $whitelist = $this->getWhitelist($match);

        /** @var Format $format */
        $format = $match->formats->first();
        $format = $format->loadMissing(["matchSettings", "matchEndCondition"]);

        $matchSettings = $format->matchSettings()->get()->map(function(MatchSetting $matchSetting) {
            $value = $matchSetting->value;
            settype($value, MatchSetting::KEYS[$matchSetting->key]);
            return [$matchSetting->key => $value];
        });

        $formatType = [
            "type" => str_replace(' ', '', FormatType::NAMES[$format->typeId])
        ];

        $teams = [];
        if ($match->group->event->isTeamEvent) {
            $teams = $match->participants->map(function (Participant $participant) {
                return [
                    "participantId" => $participant->id,
                    "teamName"      => $participant->page->name
                ];
            });
            $teams = $teams->toArray();
        }

        try {
            $this->api->startMatch(
                $port,
                $sortedMaps->toArray(),
                $matchSettings->toArray(),
                $formatType,
                $whitelist,
                $matchId,
                $format->matchEndCondition()->first()->toArray(),
                $teams
            );
        } catch (\Throwable $exception) {
            //TODO: restart server
            throw $exception;
        }

        try {
            $match->group->event->statusId = Event::STATUS_LIVE;
            $match->group->event->save();
            SendMatchStatusUpdatedEventToDiscordWebhook::dispatch($match);
        } catch (\Throwable $exception) {
            //
        }

        $dedicated->statusId = DedicatedController::CONFIGURING;
        $dedicated->save();
    }

    /**
     * @param $matchId
     * @return DedicatedController
     * @throws NoServerAvailable
     */
    public function reserve($matchId): DedicatedController
    {
        /** @var DedicatedController $dedicated */
        $dedicated = DedicatedController::where("status_id", DedicatedController::OPEN)
            ->first();

        if (!$dedicated) {
            throw new NoServerAvailable();
        }

        $dedicated->matchId = $matchId;
        $dedicated->statusId = DedicatedController::RESERVED;
        $dedicated->save();
        return $dedicated;
    }

    public function getWhitelist(MatchModel $match)
    {
        if ($match->group->event->isTeamEvent) {
            $participants = [];

            /** @var Participant[] $teams */
            $teams = $match->participants()->get();

            foreach ($teams as $team) {
                foreach ($team->lineups as $lineup) {
                    $participants[] = ["nickname" => $lineup->user->tmNickname, "participantId" => $team->id];
                }
            }
        } else {
            $participants = $match->participants()->get()->map(function(Participant $participant) {
                if ($participant->user->nickname) {
                    return ["nickname" => $participant->user->tmNickname];
                }
            });
            $participants = $participants->toArray();
        }

        $properties = EventProperty::where("event_id", $match->group->event->id)->get();

        $nonParticipants = $properties->filter(function (EventProperty $property) {
            if ($property->key == EventProperty::NON_PARTICIPANT) {
                return ["nickname" => $property->value];
            }
        });

        $nonParticipants = $nonParticipants->map(function (EventProperty $property) {
            return ["nickname" => $property->value];
        });

        return array_merge($participants, $nonParticipants->toArray());
    }

    public function updateWhitelist($matchId)
    {
        /** @var MatchModel $match */
        $match = MatchModel::where("id", $matchId)->first();

        /** @var DedicatedController $dedicatedController */
        $dedicatedController = DedicatedController::where("match_id", $matchId)->first();

        $whitelist = $this->getWhitelist($match);

        $this->api->updateWhitelist($dedicatedController->port, $whitelist);
    }

    /**
     * @param MatchModel $match
     */
    public function calculateElo(MatchModel $match)
    {
        $formats = $match->formats;

        $isTimeAttack  = $formats->filter(function (Format $format) {
            if ($format->typeId == FormatType::TIME_ATTACK_VALUE) {
                return true;
            }

            return false;
        });

        if ($isTimeAttack->count()) {
            return;
        }

        if ($this->isUserRankedMatch($match)) {
            $this->calculatePlayEloRating($match);
        } else {
            if ($this->isTeamRankedMatch($match)) {
                $this->calculateTeamEloRating($match);
            }
        }
    }

    public function cancelMatch($matchId)
    {
        /** @var DedicatedController $server */
        $server = DedicatedController::where("match_id", $matchId)->first();
        $this->api->cancelMatch($server->port);
    }

    private function isUserRankedMatch(MatchModel $match)
    {
        $eventProperties = $match->group->event->properties;
        $playProperty = $eventProperties->filter(function (EventProperty $property) {
            if ($property->key == EventProperty::PLAY_ESAC_GG_EVENT) {
                return true;
            }

            if ($property->key == EventProperty::HOURLY_SHOWDOWN) {
                return true;
            }

            if ($property->key == EventProperty::RANKED_EVENT) {
                return true;
            }

            return false;
        });

        if ($playProperty->count()) {
            return true;
        }

        return false;
    }

    private function isTeamRankedMatch(MatchModel $match)
    {
        $eventProperties = $match->group->event->properties;
        $teamProperty = $eventProperties->filter(function (EventProperty $property) {
            if ($property->key == EventProperty::WEEKLY_EVENT_MX_ID) {
                return true;
            }

            if ($property->key == EventProperty::RANKED_EVENT) {
                return true;
            }

            return false;
        });

        if ($teamProperty->count()) {
            return true;
        }

        return false;
    }

    public function calculatePlayEloRating(MatchModel $match)
    {
        //TODO: move to ranking service
        $playerCount = $match->participants->count();

        for ($playerIndex = 0; $playerIndex < $playerCount; $playerIndex++) {
            /** @var Participant $player */
            $player = $match->participants[$playerIndex];
            $oldElo = $player->user->elo;

            for ($opponentIndex = 0; $opponentIndex < $playerCount; $opponentIndex++) {
                if ($opponentIndex == $playerIndex) {
                    continue;
                }

                /** @var Participant $opponent */
                $opponent = $match->participants[$opponentIndex];

                if (!$player || !$opponent) {
                    continue;
                }

                $results = json_encode($match->totalMatchResults);
                $results = json_decode($results, true);

                $playerResult = $results[$player->id];

                if ($playerResult) {
                    $playerResult = $playerResult['result'];
                } else {
                    $playerResult = 0;
                }

                $opponentResult = $results[$opponent->id];

                if ($opponentResult) {
                    $opponentResult = $opponentResult['result'];
                } else {
                    $opponentResult = 0;
                }

                $hasWon  = $playerResult > $opponentResult;
                $isEqual = $playerResult == $opponentResult;

                $winProbability = $this->rankingService->getProbabilityToWin(
                    $oldElo,
                    $opponent->user->elo
                );

                $newElo = $this->rankingService->getNewRating(
                    $player->user->elo,
                    $winProbability,
                    $hasWon,
                    $isEqual
                );

                $ratingGained = $newElo - $player->user->elo;
                $player->user->elo = $player->user->elo + $ratingGained;
                $player->user->save();

                $eloHistory = new EloHistory();
                $eloHistory->userId     = $player->user->id;
                $eloHistory->matchId    = $match->id;
                $eloHistory->opponentId = $opponent->user->id;
                $eloHistory->oldElo     = $oldElo;
                $eloHistory->newElo     = $newElo;
                $eloHistory->save();
            }
        }
    }

    public function calculateTeamEloRating(MatchModel $match)
    {
        //TODO: move to ranking service
        $playerCount = $match->participants->count();

        for ($playerIndex = 0; $playerIndex < $playerCount; $playerIndex++) {
            /** @var Participant $player */
            $player = $match->participants[$playerIndex];
            $oldElo = $player->page->elo;

            for ($opponentIndex = 0; $opponentIndex < $playerCount; $opponentIndex++) {
                if ($opponentIndex == $playerIndex) {
                    continue;
                }

                /** @var Participant $opponent */
                $opponent = $match->participants[$opponentIndex];

                if (!$player || !$opponent) {
                    continue;
                }

                $results = json_encode($match->totalMatchResults);
                $results = json_decode($results, true);

                $hasWon  = $results[$player->id]['result'] > $results[$opponent->id]['result'];
                $isEqual = $results[$player->id]['result'] == $results[$opponent->id]['result'];

                $winProbability = $this->rankingService->getProbabilityToWin(
                    $oldElo,
                    $opponent->page->elo
                );

                $newElo = $this->rankingService->getNewRating(
                    $player->page->elo,
                    $winProbability,
                    $hasWon,
                    $isEqual
                );

                $ratingGained = $newElo - $player->page->elo;
                $player->page->elo = $player->page->elo + $ratingGained;
                $player->page->save();

                $eloHistory = new TeamEloHistory();
                $eloHistory->pageId     = $player->page->id;
                $eloHistory->matchId    = $match->id;
                $eloHistory->opponentId = $opponent->page->id;
                $eloHistory->oldElo     = $oldElo;
                $eloHistory->newElo     = $newElo;
                $eloHistory->save();
            }
        }
    }
}
