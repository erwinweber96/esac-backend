<?php


namespace Modules\Console\Console;


use Modules\Console\Entities\ScheduledAction;

class SchedulerCron30 extends SchedulerCron
{
    function getCronTypeId(): int
    {
        return ScheduledAction::TYPE_CRON_EVERY_THIRTY_MINUTES;
    }
}
