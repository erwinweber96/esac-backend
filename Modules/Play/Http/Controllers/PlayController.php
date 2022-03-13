<?php


namespace Modules\Play\Http\Controllers;


use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Event\Entities\Event;
use Modules\Event\Entities\EventDate;
use Modules\Event\Entities\EventProperty;
use Modules\Event\Entities\Participant;
use Modules\Group\Entities\Group;
use Modules\Link\Entities\Link;
use Modules\Match\Entities\MatchModel;
use Modules\Play\Entities\EloHistory;
use Modules\User\Entities\User;

/**
 * Class PlayController
 * @package Modules\Play\Http\Controllers
 */
class PlayController extends Controller
{
    public function getEvents()
    {
        return $this->getPlayEvents();
    }

    public function getPlayEvent($slug)
    {
        /** @var Event $event */
        $event = Event::where("slug", $slug)
            ->with(Event::RELATIONS)
            ->with('formats.type')
            ->with('formats.matchEndCondition')
            ->with('formats.matchSettings')
            ->with('groups.formats')
            ->with('participants.user')
            ->with('participants.user.badges')
            ->with('participants.page')
            ->with('participants.page.members')
            ->with('participants.page.members.user')
            ->with('participants.page.user')
            ->with('groups.matches.formats')
            ->first();

        if (!$event) {
            /** @var Event $event */
            $event = Event::where("id", $slug)
                ->with(Event::RELATIONS)
                ->with('formats.type')
                ->with('formats.matchEndCondition')
                ->with('formats.matchSettings')
                ->with('groups.formats')
                ->with('participants.user')
                ->with('participants.user.badges')
                ->with('participants.page')
                ->with('participants.page.members')
                ->with('participants.page.members.user')
                ->with('participants.page.user')
                ->with('groups.matches.formats')
                ->first();
        }

        $event->properties = $this->getPlayProperties($event->id);
        return $event;
    }

    public function getLeaderboard()
    {
        $eventProperties = EventProperty::where("key", EventProperty::RANKED_EVENT)
            ->orWhere("key", EventProperty::HOURLY_SHOWDOWN)
            ->orWhere("key", EventProperty::PLAY_ESAC_GG_EVENT)
            ->get(["event_id"]);

        $eventProperties = $eventProperties->map(function ($eventProperty) {
            return $eventProperty->event_id;
        });

        $events = DB::table(Event::TABLE_NAME)
            ->whereIn("id", $eventProperties->toArray())
            ->get();

        $eventIds = $events->map(function($event) {
            return $event->id;
        });

        $participants = DB::table(Participant::TABLE_NAME)
            ->whereIn("event_id", $eventIds->toArray())
            ->get();

        $userIds = $participants->map(function ($participant) {
           return $participant->user_id;
        });

        $users = DB::table(User::TABLE_NAME)
            ->whereIn("id", $userIds)
            ->get([
                "nickname",
                "elo",
                "id",
                "nat",
                "badge_id",
                "tm_nickname"
            ]);

        $users = $users->sort(function ($user1, $user2) {
            return $user1->elo <= $user2->elo ? 1 : -1;
        });

        return array_values($users->toArray());
    }

    public function getPlayerEloHistory($userId)
    {
        $history = EloHistory::where("user_id", $userId)->get();
        return $history->map(function (EloHistory $eloHistory) use ($userId) {
            $eloHistory->match = DB::table(MatchModel::TABLE_NAME)
                ->where("id", $eloHistory->matchId)
                ->first(["name", "group_id"]);
            $eloHistory->group = DB::table(Group::TABLE_NAME)
                ->where("id", $eloHistory->match->group_id)
                ->first(["name", "event_id"]);
            $eloHistory->event = DB::table(Event::TABLE_NAME)
                ->where("id", $eloHistory->group->event_id)
                ->first(["name", "slug", "type"]);
            $eloHistory->player = DB::table(User::TABLE_NAME)
                ->where("id", $userId)
                ->first(["nickname"]);
            $eloHistory->opponent = DB::table(User::TABLE_NAME)
                ->where("id", $eloHistory->opponentId)
                ->first(["nickname"]);

            return $eloHistory;
        });
    }

    private function getPlayProperties($eventId)
    {
        return EventProperty::whereIn("key", EventProperty::PLAY_PROPERTIES)
            ->where("event_id", $eventId)
            ->get();
    }

    /**
     * @param bool $withShowdowns
     * @return Collection|Event[]
     */
    private function getPlayEvents($withShowdowns = false, $withParticipants = false)
    {
        $eventIds = EventProperty::where("key", EventProperty::HOURLY_SHOWDOWN)
            ->orWhere("key", EventProperty::MATCHMAKING_LADDER)
            ->orWhere("key", EventProperty::RANKED_EVENT)
            ->orWhere("key", EventProperty::CUSTOM_SHOWDOWN)
            ->get()
            ->map(function (EventProperty $eventProperty) {
                return $eventProperty->eventId;
            });

        $events = DB::table(Event::TABLE_NAME)
            ->whereIn("id", $eventIds->toArray())
            ->orderBy("status_id", "asc")
            ->orderBy("id", "desc")
            ->limit(12)
            ->get([
                "id",
                "slug",
                "name",
                "status_id",
                "type"
            ]);


        $eventDates = DB::table(EventDate::TABLE_NAME)->whereIn("event_id", $eventIds->toArray())->get();
        $links = DB::table(Link::TABLE_NAME)->whereIn("event_id", $eventIds->toArray())->get();
        $participants =  DB::table(Participant::TABLE_NAME)->whereIn("event_id", $eventIds->toArray())->get();

        foreach ($events as $index => $event) {
            $eventId = $event->id;

            if ($withParticipants) {
                $events[$index]->participants = $participants->where("event_id", $eventId);
            }

            $events[$index]->dates = array_values($eventDates->where("event_id", $eventId)->toArray());
            $events[$index]->participantCount = $participants->where("event_id", $eventId)->count();
            $events[$index]->properties = $this->getPlayProperties($eventId);
            $events[$index]->links = $links->where("event_id", $eventId);
        }

        return $events;
    }

    public function getHourlyShowdowns()
    {
        $eventIds = EventProperty::where("key", EventProperty::HOURLY_SHOWDOWN)->get()
            ->map(function (EventProperty $eventProperty) {
                return $eventProperty->eventId;
            });

        $events = DB::table(Event::TABLE_NAME)
            ->whereIn("id", $eventIds->toArray())
            ->orderBy("id", "desc")
            ->limit(12)
            ->get([
                "id",
                "slug",
                "name",
                "status_id",
            ]);

        foreach ($events as $index => $event) {
            $eventId = $event->id;
            $eventDates = EventDate::where("event_id", $eventId)->get();
            $numberOfParticipants = DB::table(Participant::TABLE_NAME)
                ->where("event_id", $eventId)
                ->count();

            $events[$index]->dates = $eventDates->toArray();
            $events[$index]->participantCount = $numberOfParticipants;
            $events[$index]->properties = $this->getPlayProperties($eventId);
        }

        return $events;
    }

    public function getUpcomingHourlyShowdown()
    {
        $eventIds = EventProperty::where("key", EventProperty::HOURLY_SHOWDOWN)->get()
            ->map(function (EventProperty $eventProperty) {
                return $eventProperty->eventId;
            });

        $event = DB::table(Event::TABLE_NAME)
            ->whereIn("id", $eventIds->toArray())
            ->orderBy("id", "desc")
            ->where('status_id', Event::STATUS_OPEN)
            ->limit(12)
            ->first([
                "id",
                "slug",
                "name",
                "status_id",
            ]);

        if (!$event) {
            return [];
        }

        $eventId = $event->id;
        $eventDates = EventDate::where("event_id", $eventId)->get();
        $numberOfParticipants = DB::table(Participant::TABLE_NAME)
            ->where("event_id", $eventId)
            ->count();

        $event->dates = $eventDates->toArray();
        $event->participantCount = $numberOfParticipants;
        $event->properties = $this->getPlayProperties($eventId);

        return response()->json($event);
    }
}
