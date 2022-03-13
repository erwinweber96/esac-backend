<?php

namespace Modules\Play\Handlers\AchievementCompletion;

use Modules\Event\Entities\Event;
use Modules\Event\Entities\EventProperty;
use Modules\Play\Services\HourlyEventService;

abstract class AchievementCompletionHandler
{
    abstract public function handle(): int;

    protected function isHourlyShowdown(Event $event)
    {
        return $event->pageId == HourlyEventService::PAGE_ID;
    }

    protected function isMatchmakingLadder(Event $event)
    {
        $found = $event->properties->filter(function (EventProperty $property) {
            return $property->key == EventProperty::MATCHMAKING_LADDER;
        });

        return (bool)$found->count();
    }
}
