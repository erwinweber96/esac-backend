<?php


namespace Modules\Event\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Event\Entities\Event;
use Modules\Event\Entities\EventProperty;
use Modules\User\Entities\User;

/**
 * Class EventPropertyController
 * @package Modules\Event\Http\Controllers
 */
class EventPropertyController
{
    public function get($slug)
    {
        $event = DB::table(Event::TABLE_NAME)->where("slug", $slug)->first(["id"]);
        return EventProperty::where("event_id", $event->id)->get();
    }

    public function create(Request $request)
    {
        $eventProperty = new EventProperty();

        /** @var Event $event */
        $event = Event::where("slug", $request->input("slug"))->first();

        if ($event->page->user->id !== auth()->user()->id) {
            return response()->json(["errors" => [
                "message" => "Not Authorized."
            ]], Response::HTTP_UNAUTHORIZED);
        }

        $eventProperty->key      = $request->input("key");
        $eventProperty->value    = $request->input("value");
        $eventProperty->eventId  = $event->id;
        $eventProperty->readOnly = true;

        $eventProperty->save();
        return $eventProperty;
    }

    public function delete($propertyId)
    {
        /** @var EventProperty $eventProperty */
        $eventProperty = EventProperty::where("id", $propertyId)->first();

        /** @var User $user */
        $user = auth()->user();

        if ($user->id !== $eventProperty->event->page->user->id) {
            return response()->json(["errors" => [
                "message" => "Not Authorized."
            ]], Response::HTTP_UNAUTHORIZED);
        }

        return EventProperty::where("id", $propertyId)->delete();
    }
}
