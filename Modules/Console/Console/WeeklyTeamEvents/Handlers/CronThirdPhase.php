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
 * Class CronThirdPhase
 * @package Modules\Console\Console\WeeklyTeamEvents
 *
 * If 4 Teams in Group
 *      => Winner WB Final to Final
 *      => Loser WB Final to LB Final
 *      => Winner LB Semi Final to LB Final
 *
 * If 3 Teams in Group
 *      => End Event
 */
class CronThirdPhase extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'weekly_team_event:third_phase';

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
     * CronThirdPhase constructor.
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
                    case 3: $this->endEvent($group); break;
                    case 4: {
                        $this->advanceToFinal($group);
                        $this->advanceToLBFinal($group);
                        break;
                    }
                }
            }
        }
    }

    public function endEvent(Group $group)
    {
        $match = $group->matches[2];

        if ($match->statusId == MatchModel::STATUS_ENDED) {
            $group->event->statusId = Event::STATUS_ENDED;
            $group->event->save();
        }
    }

    public function advanceToFinal(Group $group)
    {
        $winnersBracketFinal = $group->matches[2];
        $final               = $group->matches[5];

        if ($winnersBracketFinal->statusId != MatchModel::STATUS_ENDED) {
            return;
        }

        if ($final->statusId != MatchModel::STATUS_UPCOMING) {
            return;
        }

        $winnerBracketWinner = $this->getMatchWinner($winnersBracketFinal);
        $final->participants()->sync([
            $winnerBracketWinner[0]
        ]);
        $final->save();
    }

    public function advanceToLBFinal(Group $group)
    {
        $winnersBracketFinal = $group->matches[2];
        $losersBracketSemi   = $group->matches[3];
        $losersBracketFinal  = $group->matches[4];

        if ($winnersBracketFinal->statusId != MatchModel::STATUS_ENDED) {
            return;
        }

        if ($losersBracketSemi->statusId != MatchModel::STATUS_ENDED) {
            return;
        }

        if ($losersBracketFinal->statusId != MatchModel::STATUS_UPCOMING) {
            return;
        }

        $winnersBracketLoser = $this->getMatchLoser($winnersBracketFinal);
        $losersBracketWinner = $this->getMatchWinner($losersBracketSemi);

        $losersBracketFinal->participants()->sync([
            $winnersBracketLoser[0], $losersBracketWinner[0]
        ]);
        $losersBracketFinal->save();
        $this->dedicatedControllerService->startMatch($losersBracketFinal->id);
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
