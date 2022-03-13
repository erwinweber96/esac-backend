<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Modules\Console\Console\ClearGlobalAlerts;
use Modules\Console\Console\CreateRestartServerAlert;
use Modules\Console\Console\GiveMatchmakingCoins;
use Modules\Console\Console\PingDedicatedControllers;
use Modules\Console\Console\RestartServers;
use Modules\Console\Console\SchedulerCron1;
use Modules\Console\Console\SchedulerCron10;
use Modules\Console\Console\SchedulerCron10080;
use Modules\Console\Console\SchedulerCron1440;
use Modules\Console\Console\SchedulerCron15;
use Modules\Console\Console\SchedulerCron30;
use Modules\Console\Console\SchedulerCron5;
use Modules\Console\Console\SchedulerCron60;
use Modules\Console\Console\SchedulerCronTest;
use Modules\Console\Console\SetMatchmakingLive;
use Modules\Console\Events\Actions\SendMatchStatusUpdatedToDiscordWebhookAction;
use Modules\Match\Jobs\SendMatchStatusUpdatedEventToDiscordWebhook;
use Modules\Page\Console\CacheArticles;
use Modules\Play\Console\CacheLeaderboard;
use Modules\Play\Console\CreateDailyMatchmakingLadders;
use Modules\Play\Console\CreateHourlyEvent;
use Modules\Play\Console\EndDailyEvents;
use Modules\Play\Console\OpenRegistrations;
use Modules\Play\Console\PrepareFinal;
use Modules\Play\Console\PrepareHourlyEventGroups;
use Modules\Play\Console\PrepareSemis;
use Modules\Play\Console\StartEvents;
use Modules\Play\Console\StartHourlyEvent;
use Modules\Post\Console\CachePosts;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //every minute
//        $schedule->command(OpenRegistrations::class)->everyMinute();
//        $schedule->command(StartEvents::class)->everyMinute();
//        $schedule->command(PrepareSemis::class)->everyMinute();
//        $schedule->command(PrepareFinal::class)->everyMinute();
//        $schedule->command(PrepareHourlyEventGroups::class)->everyMinute();

        //every five minutes
        $schedule->command(PingDedicatedControllers::class)->everyFiveMinutes();
//        $schedule->command(EndDailyEvents::class)->everyFiveMinutes();

        //every hour
//        $schedule->command(CreateHourlyEvent::class)->hourly();
//        $schedule->command(StartHourlyEvent::class)->everyMinute();

        //every day
        $schedule->command(CreateRestartServerAlert::class)->dailyAt("23:00");
        $schedule->command(RestartServers::class)->dailyAt("0:00");
        $schedule->command(ClearGlobalAlerts::class)->dailyAt("0:30");

        //matchmaking
        $schedule->command(GiveMatchmakingCoins::class)->everyFiveMinutes();
        $schedule->command(SetMatchmakingLive::class)->everyFiveMinutes();
        $schedule->command(CreateDailyMatchmakingLadders::class)->dailyAt("0:00");

        //cache
        $schedule->command(CacheArticles::class)->hourly();
        $schedule->command(CachePosts::class)->hourly();
        $schedule->command(CacheLeaderboard::class)->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
