<?php


namespace Modules\Console\Console;


use Modules\Console\Entities\ScheduledAction;

/**
 * Class SchedulerCronTest
 * @package Modules\Console\Console
 */
class SchedulerCronTest extends SchedulerCron
{
    function getCronTypeId(): int
    {
        return ScheduledAction::TYPE_CRON_EVERY_DAY;
    }
}
