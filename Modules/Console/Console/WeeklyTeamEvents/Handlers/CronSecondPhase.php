<?php


namespace Modules\Console\Console\WeeklyTeamEvents\Handlers;


use Carbon\Carbon;
use Illuminate\Console\Command;
use Modules\Console\Console\WeeklyTeamEvents\Services\WeeklyTeamEventService;
use Modules\Console\Services\DedicatedControllerService;
use Modules\Event\Entities\Event;
use Modules\Group\Entities\Group;
use Modules\Match\Entities\MatchModel;
use Modules\Match\Entities\MatchResult;

/**
 * Class CronSecondPhase
 * @package Modules\Console\Console\WeeklyTeamEvents
 *
 * If 4 teams in group and semis over
 *      => Semi Winners to WB Final
 *      => Semi Losers to LB Semi Final
 *
 * If 3 teams in group
 *      => Semi Winners to Final
 *
 * If 2 teams
 *      => End event
 */
class CronSecondPhase extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'weekly_team_event:second_phase';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description.';

    /** @var DedicatedControllerService $dedicatedControllerService */
    private $dedicatedControllerService;

    /** @var WeeklyTeamEventService $weeklyTeamEventService */
    private $weeklyTeamEventService;

    /**
     * CronSecondPhase constructor.
     * @param DedicatedControllerService $dedicatedControllerService
     * @param WeeklyTeamEventService $weeklyTeamEventService
     */
    public function __construct(DedicatedControllerService $dedicatedControllerService, WeeklyTeamEventService $weeklyTeamEventService)
    {
        $this->dedicatedControllerService = $dedicatedControllerService;
        $this->weeklyTeamEventService = $weeklyTeamEventService;
    }


    public function handle()
    {
        $eventsToHandle = $this->weeklyTeamEventService->getWeeklyEvents(Event::STATUS_LIVE);

        /** @var Event $event */
        foreach ($eventsToHandle as $event) {
            foreach ($event->groups as $group) {
                switch ($group->participants->count())
                {
                    case 2: $this->endEvent($group); break;
                    case 3: $this->advanceToFinal($group); break;
                    case 4: {
                        $this->advanceToWBFinal($group);
                        $this->advanceToLBSemiFinal($group);
                        break;
                    }
                }
            }
        }
    }

    public function endEvent(Group $group)
    {
        $match = $group->matches[0];

        if ($match->statusId == MatchModel::STATUS_ENDED) {
            $group->event->statusId = Event::STATUS_ENDED;
            $group->event->save();
        }
    }

    public function advanceToFinal(Group $group)
    {
        $semiFinal1 = $group->matches[0];
        $semiFinal2 = $group->matches[1];
        $final      = $group->matches[2];

        if ($semiFinal1->statusId != MatchModel::STATUS_ENDED) {
            return;
        }

        if ($semiFinal2->statusId != MatchModel::STATUS_ENDED) {
            return;
        }

        if ($final->statusId != MatchModel::STATUS_UPCOMING) {
            return;
        }

        $semiFinal1Winner = $this->getMatchWinner($semiFinal1);
        $semiFinal2Winner = $this->getMatchWinner($semiFinal2);

        $final->participants()->sync([
            $semiFinal1Winner[0], $semiFinal2Winner[0]
        ]);
        $final->save();
        $this->dedicatedControllerService->startMatch($final->id);
    }

    public function advanceToWBFinal(Group $group)
    {
        $semiFinal1          = $group->matches[0];
        $semiFinal2          = $group->matches[1];
        $winnersBracketFinal = $group->matches[2];

        if ($semiFinal1->statusId != MatchModel::STATUS_ENDED) {
            return;
        }

        if ($semiFinal2->statusId != MatchModel::STATUS_ENDED) {
            return;
        }

        if ($winnersBracketFinal->statusId != MatchModel::STATUS_UPCOMING) {
            return;
        }

        $semiFinal1Winner = $this->getMatchWinner($semiFinal1);
        $semiFinal2Winner = $this->getMatchWinner($semiFinal2);

        $winnersBracketFinal->participants()->sync([
            $semiFinal1Winner[0], $semiFinal2Winner[0]
        ]);
        $winnersBracketFinal->save();
        $this->dedicatedControllerService->startMatch($winnersBracketFinal->id);
    }

    public function advanceToLBSemiFinal(Group $group)
    {
        $semiFinal1             = $group->matches[0];
        $semiFinal2             = $group->matches[1];
        $losersBracketSemiFinal = $group->matches[3];

        if ($semiFinal1->statusId != MatchModel::STATUS_ENDED) {
            return;
        }

        if ($semiFinal2->statusId != MatchModel::STATUS_ENDED) {
            return;
        }

        if ($losersBracketSemiFinal->statusId != MatchModel::STATUS_UPCOMING) {
            return;
        }

        $semiFinal1Loser = $this->getMatchLoser($semiFinal1);
        $semiFinal2Loser = $this->getMatchLoser($semiFinal2);

        $losersBracketSemiFinal->participants()->sync([
            $semiFinal1Loser[0], $semiFinal2Loser[0]
        ]);
        $losersBracketSemiFinal->save();
        $this->dedicatedControllerService->startMatch($losersBracketSemiFinal->id);
    }

    private function getMatchWinner(MatchModel $match)
    {
        $results = collect($match->totalMatchResults);
        $results = $results->sortByDesc('result', SORT_NATURAL);
        $winners = $results->take(1);
        $winners = $winners->map(function (?MatchResult $result, $participantId) {
            return $participantId;
        });
        $arrayWithObject = array_values((array)$winners);
        return array_values((array)$arrayWithObject[0]);
    }

    private function getMatchLoser(MatchModel $match)
    {
        $results = collect($match->totalMatchResults);
        $results = $results->sortBy('result', SORT_NATURAL);
        $winners = $results->take(1);
        $winners = $winners->map(function (?MatchResult $result, $participantId) {
            return $participantId;
        });
        $arrayWithObject = array_values((array)$winners);
        return array_values((array)$arrayWithObject[0]);
    }
}
