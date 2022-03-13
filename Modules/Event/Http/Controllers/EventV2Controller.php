<?php


namespace Modules\Event\Http\Controllers;


use Illuminate\Support\Facades\DB;
use Modules\Event\Entities\Event;
use Modules\Event\Entities\EventDate;
use Modules\Event\Entities\EventFaq;
use Modules\Event\Entities\EventModerator;
use Modules\Event\Entities\Lineup;
use Modules\Event\Entities\Participant;
use Modules\Event\Jobs\CacheEventOverview;
use Modules\Group\Entities\Format;
use Modules\Group\Entities\Group;
use Modules\Group\Entities\GroupContainer;
use Modules\Group\Repositories\GroupV2Repository;
use Modules\Link\Entities\Link;
use Modules\Map\Entities\MapPool;
use Modules\Match\Entities\MatchModel;
use Modules\Page\Entities\Page;
use Modules\Page\Entities\PageMember;
use Modules\User\Entities\Discord;
use Modules\User\Entities\User;

/**
 * Class EventV2Controller
 * @package Modules\Event\Http\V2
 */
class EventV2Controller
{
    /** @var GroupV2Repository $groupRepository */
    private $groupRepository;

    /**
     * @param GroupV2Repository $groupRepository
     */
    public function __construct(GroupV2Repository $groupRepository)
    {
        $this->groupRepository = $groupRepository;
    }

    public function getEventOverview($slug)
    {
        $event = DB::table(Event::TABLE_NAME)
            ->where("slug", $slug)
            ->first();

        if (!$event) {
            $event = DB::table(Event::TABLE_NAME)
                ->where("id", $slug)
                ->first();
        }

        $eventId = $event->id;

        $numberOfParticipants = DB::table(Participant::TABLE_NAME)
            ->where("event_id", $eventId)
            ->count();

        $links = DB::table(Link::TABLE_NAME)
            ->where("event_id", $eventId)
            ->get();

        $mapPools = DB::table(MapPool::TABLE_NAME)
            ->where("event_id", $eventId)
            ->get();

        $formats = DB::table(Format::TABLE_NAME)
            ->where("event_id", $eventId)
            ->get();

        $dates = DB::table(EventDate::TABLE_NAME)
            ->where("event_id", $eventId)
            ->get();

        $moderators = DB::table(EventModerator::TABLE_NAME)
            ->where("event_id", $eventId)
            ->get();

        $groupContainers = DB::table(GroupContainer::TABLE_NAME)
            ->where("event_id", $eventId)
            ->get();

        foreach ($moderators as $moderator) {
            $user = DB::table(User::TABLE_NAME)
                ->where("id", $moderator->user_id)
                ->first([
                    "id",
                    "nickname",
                    "nat",
                    "badge_id",
                    "tm_nickname",
                    "elo"
                ]);

            $moderator->user = $user;
        }

        $page = DB::table(Page::TABLE_NAME)
            ->where("id", $event->page_id)
            ->first([
                "id",
                "name",
                "user_id",
                "slug",
                "created_at"
            ]);

        $pageUser = DB::table(User::TABLE_NAME)
            ->where("id", $page->user_id)
            ->first([
                "id",
                "nickname",
                "nat",
                "badge_id",
                "tm_nickname",
                "elo"
            ]);

        $page->user = $pageUser;

        $event = (array)$event;

        $event['number_of_participants'] = $numberOfParticipants;
        $event['links'] = $links;
        $event['map_pools'] = $mapPools;
        $event['formats'] = $formats;
        $event['dates'] = $dates;
        $event['moderators'] = $moderators;
        $event['page'] = $page;
        $event['group_containers'] = $groupContainers;

        return $event;
    }

    public function getEventParticipants($slug)
    {
        $event = DB::table(Event::TABLE_NAME)
            ->where("slug", $slug)
            ->first();

        $eventId = $event->id;

        $participants = DB::table(Participant::TABLE_NAME)
            ->where("event_id", $eventId)
            ->get();

        if ($event->is_team_event == 1) {
            $pageIds = $participants->map(function ($participant) {
                return $participant->page_id;
            });

            $pages = DB::table(Page::TABLE_NAME)
                ->whereIn("id", $pageIds->toArray())
                ->get([
                    'id',
                    'user_id',
                    'type_id',
                    'name',
                    'slug'
                ]);

            $allPageMembers = PageMember::whereIn("page_id", $pageIds)
                ->with("user")
                ->get();

            $allLineups = Lineup::whereIn("participant_id", $pageIds)
                ->with("pageMember")
                ->with("pageMember.user")
                ->get();
        } else {
            $userIds = $participants->map(function ($participant) {
                return $participant->user_id;
            });

            $users = DB::table(User::TABLE_NAME)
                ->whereIn("id", $userIds->toArray())
                ->get();
        }

        foreach ($participants as $participant) {
            if ($participant->type == 'user') {
                $user = $users
                    ->where("id", $participant->user_id);
                $user = $user->map(function($u) {
                    $data = new \stdClass();
                    $data->id =    $u->id;
                    $data->nickname=    $u->nickname;
                    $data->elo =    $u->elo;
                    $data->nat =    $u->nat;
                    $data->tm_nickname =    $u->tm_nickname;
                    $data->badge_id =    $u->badge_id;
                    return $data;
                });
                $user = $user->first();

                $discord = DB::table(Discord::TABLE_NAME)
                    ->where("user_id", $user->id)
                    ->first();

                $user->discord = $discord;
                $participant->user = $user;
            }

            if ($participant->type == 'page') {
                $page = $pages
                    ->where("id", $participant->page_id)
                    ->first();

                $pageUser = DB::table(User::TABLE_NAME)
                    ->where("id", $page->user_id)
                    ->first([
                        "id",
                        "nickname",
                        "elo",
                        "nat",
                        "tm_nickname",
                        "badge_id"
                    ]);

                $pageMembers = $allPageMembers->where("page_id", $page->id);

                $page->user = $pageUser;
                $page->members = array_values($pageMembers->toArray());

                $lineups = $allLineups->where("participant_id", $page->id);

                $participant->page = $page;
                $participant->lineups = $lineups;
            }
        }

        return $participants->toJson();
    }

    public function getEventGroups($slug)
    {
        $event = DB::table(Event::TABLE_NAME)
            ->where("slug", $slug)
            ->first(['id']);

        $eventId = $event->id;

        return $this->groupRepository->getEventGroups($eventId);
    }

    public function getGroupMatches($groupId)
    {
        return MatchModel::where("group_id", $groupId)->get();
    }

    public function getFaq($slug)
    {
        $event = DB::table(Event::TABLE_NAME)
            ->where("slug", $slug)
            ->first();

        $eventId = $event->id;

        return EventFaq::where("event_id", $eventId)->get();
    }

    public function getEventFormats($slug)
    {
        $event = DB::table(Event::TABLE_NAME)
            ->where("slug", $slug)
            ->first();

        $eventId = $event->id;

        return Format::where("event_id", $eventId)
            ->with("type")
            ->with("matchEndCondition")
            ->with("matchSettings")
            ->get();
    }

    public function cacheEventOverview($slug)
    {
        $event = Event::where("slug", $slug)->first();

        if (!$event) {
            return;
        }

        CacheEventOverview::dispatch($event);
    }
}
