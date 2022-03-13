<?php


namespace Modules\Console\Console\WeeklyTeamEvents\Handlers;


use Carbon\Carbon;
use Illuminate\Console\Command;
use Modules\Console\Console\WeeklyTeamEvents\Services\WeeklyTeamEventService;
use Modules\Event\Entities\Event;
use Modules\Match\Entities\MatchModel;

/**
 * Class CronFifthPhase
 * @package Modules\Console\Console\WeeklyTeamEvents
 *
 * If 4 teams in group
 *      => end event
 */
class CronFifthPhase extends Command
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

    /** @var WeeklyTeamEventService $weeklyTeamEventService */
    private $weeklyTeamEventService;

    /**
     * CronFifthPhase constructor.
     * @param WeeklyTeamEventService $weeklyTeamEventService
     */
    public function __construct(WeeklyTeamEventService $weeklyTeamEventService)
    {
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
                    case 4: {
                        if ($group->matches[5]->statusId == MatchModel::STATUS_ENDED) {
                            $group->event->statusId = Event::STATUS_ENDED;
                            $group->event->save();
                        }
                    }
                }
            }
        }
    }
}
