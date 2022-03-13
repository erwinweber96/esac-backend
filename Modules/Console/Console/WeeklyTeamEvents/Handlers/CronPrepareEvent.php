<?php


namespace Modules\Console\Console\WeeklyTeamEvents\Handlers;


use Carbon\Carbon;
use Illuminate\Console\Command;
use Modules\Console\Console\WeeklyTeamEvents\CreateGroups;
use Modules\Console\Console\WeeklyTeamEvents\CreateMatches;
use Modules\Console\Console\WeeklyTeamEvents\Services\WeeklyTeamEventService;
use Modules\Console\Services\DedicatedControllerService;
use Modules\Event\Entities\Event;
use Modules\Match\Entities\MatchModel;

class CronPrepareEvent extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'weekly_team_event:prepare';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description.';


    /** @var CreateGroups $createGroups */
    private $createGroups;

    /** @var CreateMatches $createMatches */
    private $createMatches;

    /** @var DedicatedControllerService $dedicatedControllerService */
    private $dedicatedControllerService;

    /** @var WeeklyTeamEventService $weeklyTeamEventService */
    private $weeklyTeamEventService;

    /**
     * CronPrepareEvent constructor.
     * @param CreateGroups $createGroups
     * @param CreateMatches $createMatches
     * @param DedicatedControllerService $dedicatedControllerService
     * @param WeeklyTeamEventService $weeklyTeamEventService
     */
    public function __construct(CreateGroups $createGroups, CreateMatches $createMatches, DedicatedControllerService $dedicatedControllerService, WeeklyTeamEventService $weeklyTeamEventService)
    {
        $this->createGroups = $createGroups;
        $this->createMatches = $createMatches;
        $this->dedicatedControllerService = $dedicatedControllerService;
        $this->weeklyTeamEventService = $weeklyTeamEventService;
    }


    public function handle()
    {
        $eventsToPrepare = $this->weeklyTeamEventService->getWeeklyEvents(Event::STATUS_OPEN);

        /** @var Event $event */
        foreach ($eventsToPrepare as $event) {
            $groups = $this->createGroups->execute($event);

            foreach ($groups as $group) {
                $matches = $this->createMatches->execute($group);

                if (count($matches) == 0) {
                    continue;
                }

                try {
                    $this->dedicatedControllerService->startMatch($matches[0]->id);
                } catch (\Throwable $exception) {
                    \Sentry\captureException($exception);
                }

                /*
                 * In case it's not a final only group.
                 */
                if (count($matches) != 1) {
                    if ($matches[1]->participants->count() == 1) {
                        $matches[1]->statusId = MatchModel::STATUS_ENDED;
                        $matches[1]->save();
                    } else {
                        sleep(1);
                        try {
                            $this->dedicatedControllerService->startMatch($matches[1]->id);
                        } catch (\Throwable $exception) {
                            \Sentry\captureException($exception);
                        }
                    }
                }
            }

            $event->statusId = Event::STATUS_LIVE;
            $event->save();
        }
    }
}
