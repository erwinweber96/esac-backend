<?php


namespace Modules\Console\Console;


use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Modules\Console\Entities\ScheduledAction;
use Modules\Console\Scheduler\Actions\ScheduledActionHandler;
use Modules\Console\Scheduler\Actions\ScheduledActionHandlerData;

/**
 * Class SchedulerCron
 * @package Modules\Console\Console
 */
abstract class SchedulerCron extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'scheduled_actions:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs the esac console scheduled actions.';

    abstract function getCronTypeId(): int;


    /**
     *  Gets all active actions of the extended type and executes them.
     */
    public function handle()
    {
        $scheduledActions = $this->getActiveScheduledActions();

        foreach ($scheduledActions as $scheduledAction) {
            $this->executeAction($scheduledAction);
        }
    }

    /**
     * @return ScheduledAction[]|Collection
     */
    protected function getActiveScheduledActions()
    {
        $scheduledActions = ScheduledAction::where("active", true)
            ->where("cron_type_id", $this->getCronTypeId())
            ->get();

        $scheduledActions = $scheduledActions->filter(function (ScheduledAction $scheduledAction) {
            if ($actionDateStart = $scheduledAction->actionDateStart) {
                $actionDateStart = new Carbon($actionDateStart);
                if ($actionDateStart->greaterThanOrEqualTo(Carbon::now())) {
                    return false;
                }
            }

            if ($actionDateEnd = $scheduledAction->actionDateEnd) {
                $actionDateEnd = new Carbon($actionDateEnd);
                if ($actionDateEnd->lessThanOrEqualTo(Carbon::now())) {
                    return false;
                }
            }

            return true;
        });

        return $scheduledActions;
    }

    /**
     * @param ScheduledAction $scheduledAction
     */
    protected function executeAction(ScheduledAction $scheduledAction)
    {
        $handlerData = new ScheduledActionHandlerData();
        $handlerData->setData($scheduledAction->data);

        /** @var ScheduledActionHandler $scheduledActionHandler */
        $scheduledActionHandler = app($scheduledAction->class);

        $scheduledActionHandler->setData($handlerData);
        $scheduledActionHandler->setFilters($scheduledAction->scheduledActionFilters);
        $scheduledActionHandler->run();

        if ($scheduledAction->typeId == ScheduledAction::TYPE_SINGLE_ACTION) {
            $scheduledAction->active = false;
            $scheduledAction->save();
        }
    }
}
