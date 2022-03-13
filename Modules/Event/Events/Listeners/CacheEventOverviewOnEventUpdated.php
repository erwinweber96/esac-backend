<?php

namespace Modules\Event\Events\Listeners;

use Modules\Event\Events\EventUpdated;
use Modules\Event\Jobs\CacheEventOverview;

class CacheEventOverviewOnEventUpdated
{
    public function handle(EventUpdated $eventUpdated)
    {
        CacheEventOverview::dispatch($eventUpdated->event);
    }
}
