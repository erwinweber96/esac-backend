<?php


namespace Modules\Console\Console\WeeklyTeamEvents\Services;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Modules\Event\Entities\Event;

class WeeklyTeamEventService
{
    const WEEKLY_EVENTS_PAGE_ID = 161;

    /**
     * @param $statusId
     * @return Event[]|Collection
     */
    public function getWeeklyEvents($statusId)
    {
        $events = Event::where("status_id", $statusId)
            ->where("page_id", WeeklyTeamEventService::WEEKLY_EVENTS_PAGE_ID)
            ->get();

        return $events->filter(function (Event $event) {
            $eventStartDate = $event
                ->dates
                ->where('name', '=', 'event_start')
                ->first();

            $eventStartDate = new Carbon($eventStartDate->date);
            if ($eventStartDate->lte(Carbon::now())) {
                return true;
            }

            return false;
        });
    }
}
