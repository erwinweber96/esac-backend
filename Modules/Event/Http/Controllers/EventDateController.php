<?php


namespace Modules\Event\Http\Controllers;


use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Event\Entities\Event;
use Modules\Event\Entities\EventDate;
use Modules\User\Entities\User;

/**
 * Class EventDateController
 * @package Modules\Event\Http\Controllers
 */
class EventDateController
{
    /**
     * @param $slug
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getEventDates($slug)
    {
        /** @var Event $event */
        $event = DB::table(Event::TABLE_NAME)
            ->where("slug", $slug)
            ->first();

        return EventDate::where("event_id", $event->id)->get();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Http\JsonResponse|EventDate
     * @throws \Exception
     */
    public function saveDate(Request $request)
    {
        $eventId = $request->input("eventId");

        /** @var Event $event */
        $event = Event::where("id", $eventId)->first();

        if (!$this->hasRights($event)) {
            return response()->json([
                "errors" => [
                    "message" => ["Not Authorized"]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        $name           = $request->input("name");
        $date           = $request->input("date");
        $isStartDate    = $request->input("isStartDate");
        $isEndDate      = $request->input("isEndDate");
        $isActionDate   = $request->input("isActionDate");

        return EventDate::create([
            "name"           => $name,
            "date"           => new Carbon($date),
            "is_start_date"  => $isStartDate ?? false,
            "is_end_date"    => $isEndDate ?? false,
            "is_action_date" => $isActionDate ?? false,
            "event_id"       => $eventId
        ]);
    }

    /**
     * @param $id
     * @return bool|\Illuminate\Http\JsonResponse|null
     * @throws \Exception
     */
    public function deleteDate($id)
    {
        /** @var EventDate $date */
        $date = EventDate::where("id", $id)->first();

        /** @var Event $event */
        $event = Event::where("id", $date->eventId)->first();

        if (!$this->hasRights($event)) {
            return response()->json([
                "errors" => [
                    "message" => ["Not Authorized"]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $date->delete();
    }

    /**
     * @param Event $event
     * @return bool
     */
    private function hasRights(Event $event)
    {
        if (!auth()->user()) {
            return false;
        }

        /** @var User $user */
        $user = auth()->user();

        if ($event->page->user->id == $user->id) {
            return true;
        }

        return false;
    }
}
