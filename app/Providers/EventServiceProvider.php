<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Modules\Console\Events\Actions\DemotePlayers;
use Modules\Console\Events\Actions\GiveChallengerCoins;
use Modules\Console\Events\Actions\HandleMultiMapSeedingPhase;
use Modules\Console\Events\Actions\PromotePlayers;
use Modules\Console\Events\Actions\SendMatchStatusUpdatedToDiscordWebhookAction;
use Modules\Console\Events\MatchEnded;
use Modules\Event\Events\Listeners\CacheEventOverviewOnEventCreated;
use Modules\Event\Events\Listeners\CacheEventOverviewOnEventUpdated;
use Modules\Event\Events\Listeners\CachePlayEventsListener;
use Modules\Match\Events\MatchStatusUpdated;
use Modules\Event\Console\CacheEventsOverview;
use Modules\Event\Console\CacheFeaturedEvents;
use Modules\Event\Events\EventCreated;
use Modules\Event\Events\EventUpdated;
use Modules\Match\Jobs\SendMatchStatusUpdatedEventToDiscordWebhook;
use Modules\Play\Console\CacheLeaderboard;
use Modules\Play\Events\Listeners\DispatchMatchStreamedAchievementVerificationJob;
use Modules\Play\Events\Listeners\DropCase;
use Modules\Post\Console\CachePosts;
use Modules\Post\Events\PostUpdated;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        MatchEnded::class => [
            SendMatchStatusUpdatedToDiscordWebhookAction::class,
//            HandleMultiMapSeedingPhase::class,
//            GiveChallengerCoins::class,
//            PromotePlayers::class,
//            DemotePlayers::class,
            CacheLeaderboard::class,
            DropCase::class,
            DispatchMatchStreamedAchievementVerificationJob::class,
        ],
        PostUpdated::class => [
            CachePosts::class
        ],
        EventUpdated::class => [
            CacheEventsOverview::class,
            CacheFeaturedEvents::class,
            CachePlayEventsListener::class,
            CacheEventOverviewOnEventUpdated::class
        ],
        EventCreated::class => [
            CacheEventsOverview::class,
            CacheFeaturedEvents::class,
            CacheEventOverviewOnEventCreated::class
        ],
        MatchStatusUpdated::class => [
            SendMatchStatusUpdatedToDiscordWebhookAction::class
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
