<?php


namespace Modules\Console\Console;


use Modules\Console\Entities\ScheduledAction;

class SchedulerCron1 extends SchedulerCron
{
    function getCronTypeId(): int
    {
        return ScheduledAction::TYPE_CRON_EVERY_MINUTE;
    }
}
