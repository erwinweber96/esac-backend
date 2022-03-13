<?php

namespace Modules\Play\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Modules\Event\Entities\Event;
use Modules\Event\Entities\EventDate;
use Modules\Event\Entities\EventProperty;
use Modules\Event\Entities\Participant;
use Modules\Group\Entities\Format;
use Modules\Group\Entities\Group;
use Modules\Group\Entities\MatchSetting;

class PlayV2Controller
{
    public function getShowdown($slug)
    {
        /** @var Event $event */
        $event = DB::table(Event::TABLE_NAME)
            ->where("slug", $slug)
            ->orWhere("id", $slug)
            ->first();

        /** @var EventProperty[] $eventProperties */
        $eventProperties = DB::table(EventProperty::TABLE_NAME)
            ->where("event_id", $event->id)
            ->whereIn("key", EventProperty::PLAY_PROPERTIES)
            ->get();

        /** @var EventDate[] $eventDates */
        $eventDates = DB::table(EventDate::TABLE_NAME)
            ->where("event_id", $event->id)
            ->get();

        /** @var Group[] $groups */
        $groups = DB::table(Group::TABLE_NAME)
            ->where("event_id", $event->id)
            ->get();

        $participants = DB::table(Participant::TABLE_NAME)
            ->where("event_id", $event->id)
            ->get();

        $formats = DB::table(Format::TABLE_NAME)
            ->where("event_id", $event->id)
            ->get();

        foreach ($formats as &$format) {
            $matchSettings = DB::table(MatchSetting::TABLE_NAME)
                ->where("format_id", $format->id)
                ->get();

            $format->match_settings = $matchSettings;
        }

        $event->properties = $eventProperties;
        $event->dates = $eventDates;
        $event->groups = $groups;
        $event->participants = $participants;
        $event->formats = $formats;

        return response()->json($event);
    }
}
