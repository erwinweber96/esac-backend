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
 * Class CronFourthPhase
 * @package Modules\Console\Console\WeeklyTeamEvents
 *
 * If 4 in group
 *      => WB Final to Final
 */
class CronFourthPhase extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'weekly_team_event:fourth_phase';

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
     * CronFourthPhase constructor.
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
                    case 4: $this->advanceToFinal($group); break;
                }
            }
        }
    }

    public function advanceToFinal(Group $group)
    {
        $losersBracketFinal  = $group->matches[4];
        $final               = $group->matches[5];

        if ($losersBracketFinal->statusId != MatchModel::STATUS_ENDED) {
            return;
        }

        if ($final->statusId != MatchModel::STATUS_UPCOMING) {
            return;
        }

        $losersBracketWinner = $this->getMatchWinner($losersBracketFinal);
        $final->participants()->sync([
            $losersBracketWinner[0], $final->participants[0]->id
        ]);
        $final->save();
        $this->dedicatedControllerService->startMatch($final->id);
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
}
