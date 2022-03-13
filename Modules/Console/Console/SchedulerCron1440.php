<?php


namespace Modules\Console\Console;


use Modules\Console\Entities\ScheduledAction;

/**
 * Class SchedulerCron1440
 * @package Modules\Console\Console
 *
 * Daily Cron
 */
class SchedulerCron1440 extends SchedulerCron
{
    function getCronTypeId(): int
    {
        return ScheduledAction::TYPE_CRON_EVERY_DAY;
    }
}
