<?php


namespace Modules\Event\Events\Listeners;


use Modules\Play\Jobs\CachePlayEventsJob;

class CachePlayEventsListener
{
    public function handle()
    {
        CachePlayEventsJob::dispatch();
    }
}
