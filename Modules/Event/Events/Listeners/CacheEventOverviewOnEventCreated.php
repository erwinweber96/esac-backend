<?php

namespace Modules\Event\Events\Listeners;

use Modules\Event\Events\EventCreated;
use Modules\Event\Jobs\CacheEventOverview;

class CacheEventOverviewOnEventCreated
{
    public function handle(EventCreated $eventCreated)
    {
        CacheEventOverview::dispatch($eventCreated->event);
    }
}
