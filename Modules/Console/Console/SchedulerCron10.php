<?php


namespace Modules\Console\Console;


use Modules\Console\Entities\ScheduledAction;

class SchedulerCron10 extends SchedulerCron
{
    function getCronTypeId(): int
    {
        return ScheduledAction::TYPE_CRON_EVERY_TEN_MINUTES;
    }
}
