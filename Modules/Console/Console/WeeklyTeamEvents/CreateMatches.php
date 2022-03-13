<?php


namespace Modules\Console\Console\WeeklyTeamEvents;


use Illuminate\Support\Collection;
use Modules\Event\Entities\Event;
use Modules\Event\Entities\EventDate;
use Modules\Event\Entities\EventProperty;
use Modules\Event\Entities\Participant;
use Modules\Group\Entities\Group;
use Modules\Map\Entities\MapPool;
use Modules\Map\Entities\MapPoolOrder;
use Modules\Map\ManiaExchange\Entities\Track;
use Modules\Match\Entities\MatchModel;
use Modules\Match\Repositories\MatchRepository;

/**
 * Class CreateMatches
 * @package Modules\Console\Console\WeeklyTeamEvents
 *
 * if 4 players, regular double elimination format
 * if 3 players, single elimination
 * if 2 players, final
 * if 1 players, no match
 */
class CreateMatches
{
    private MatchRepository $matchRepository;

    /**
     * CreateMatches constructor.
     * @param MatchRepository $matchRepository
     */
    public function __construct(MatchRepository $matchRepository)
    {
        $this->matchRepository = $matchRepository;
    }

    /**
     * @param Group $group
     * @return MatchModel[]
     */
    public function execute(Group $group)
    {
        switch($group->participants->count()) {
            case 4: return $this->createDoubleEliminationMatches($group);
            case 3: return $this->createSingleEliminationMatches($group);
            case 2: return $this->createFinalOnly($group);
        }

        return [];
    }

    public function createDoubleEliminationMatches(Group $group)
    {
        $semiFinal1             = $this->createMatch($group, "Semi-Final-1");
        $semiFinal2             = $this->createMatch($group, "Semi-Final-2");
        $winnersBracketFinal    = $this->createMatch($group, "WB-Final");
        $losersBracketSemiFinal = $this->createMatch($group, "LB-Semi-Final");
        $losersBracketFinal     = $this->createMatch($group, "LB-Final");
        $final                  = $this->createMatch($group, "Final");

        //add teams to semis
        $participants = $group->participants->shuffle();
        $participants = $participants->chunk(2);

        /** @var Participant[]|Collection $semiFinal1Participants */
        $semiFinal1Participants = $participants[0];

        /** @var Participant[]|Collection $semiFinal2Participants */
        $semiFinal2Participants = $participants[1];

        $semiFinal1Participants = $semiFinal1Participants->map(function (Participant $participant) {
            return $participant->id;
        });

        $semiFinal2Participants = $semiFinal2Participants->map(function (Participant $participant) {
            return $participant->id;
        });

        $semiFinal1 = $this->matchRepository->syncParticipants($semiFinal1, $semiFinal1Participants->toArray());
        $semiFinal2 = $this->matchRepository->syncParticipants($semiFinal2, $semiFinal2Participants->toArray());

        return [
            $semiFinal1,
            $semiFinal2,
            $winnersBracketFinal,
            $losersBracketSemiFinal,
            $losersBracketFinal,
            $final
        ];
    }

    public function createSingleEliminationMatches(Group $group)
    {
        $semiFinal1 = $this->createMatch($group, "Semi-Final-1");
        $semiFinal2 = $this->createMatch($group, "Semi-Final-2");
        $final      = $this->createMatch($group, "Final");

        //add teams to semis
        $participants = $group->participants->shuffle();
        $participants = $participants->chunk(2);

        /** @var Participant[]|Collection $semiFinal1Participants */
        $semiFinal1Participants = $participants[0];

        /** @var Participant[]|Collection $semiFinal2Participants */
        $semiFinal2Participants = $participants[1];

        $semiFinal1Participants = $semiFinal1Participants->map(function (Participant $participant) {
            return $participant->id;
        });

        $semiFinal2Participants = $semiFinal2Participants->map(function (Participant $participant) {
            return $participant->id;
        });

        $semiFinal1 = $this->matchRepository->syncParticipants($semiFinal1, $semiFinal1Participants->toArray());
        $semiFinal2 = $this->matchRepository->syncParticipants($semiFinal2, $semiFinal2Participants->toArray());

        return [
            $semiFinal1,
            $semiFinal2,
            $final
        ];
    }

    public function createFinalOnly(Group $group)
    {
        $final = $this->createMatch($group, "Final");

        //add teams to final
        $participants = $group->participants->map(function (Participant $participant) {
            return $participant->id;
        });

        $final = $this->matchRepository->syncParticipants($final, $participants->toArray());

        return [
            $final
        ];
    }

    /**
     * @param Group $group
     * @param $matchName
     * @return MatchModel
     */
    private function createMatch(Group $group, $matchName)
    {
        $match = new MatchModel();

        /** @var EventDate $date */
        $date = $group->event->dates->where('name', 'event_start')->first();

        $match->name      = $matchName;
        $match->date      = $date->date;
        $match->mapPoolId = $group->event->mapPools()->first()->id;
        $match->groupId   = $group->id;
        $match->save();

        $match->formats()->sync([
            $group->event->formats[0]->id
        ]);

        $this->createMapPoolOrders($group->event, $match);

        return $match;
    }

    private function createMapPoolOrders(Event $event, MatchModel $match)
    {
        /** @var EventProperty $mxMapId */
        $mxMapId = $event
            ->properties
            ->where('key', EventProperty::WEEKLY_EVENT_MX_ID)
            ->first();

        /** @var MapPool $mapPool */
        $mapPool = $event->mapPools()->first();

        /** @var Track[] $tracks */
        $tracks = $mapPool->mxData->getTracks();

        foreach ($tracks as $track) {
            $mapPoolOrder = new MapPoolOrder();
            $mapPoolOrder->matchId   = $match->id;
            $mapPoolOrder->mapPoolId = $mapPool->id;
            $mapPoolOrder->mxMapId   = $track['id'];
            $mapPoolOrder->order     = $track['id'] == $mxMapId->value ? 1 : 0;
            $mapPoolOrder->save();
        }
    }
}
