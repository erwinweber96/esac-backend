<?php

namespace Modules\Console\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Console\Entities\ApiToken;
use Modules\Console\Entities\ConsoleAccess;
use Modules\Console\Entities\DedicatedController;
use Modules\Console\Exceptions\NoServerAvailable;
use Modules\Console\Services\DedicatedControllerService;
use Modules\Console\Traits\RequiresConsoleAccess;
use Modules\Event\Builders\EventBuilder;
use Modules\Event\Entities\Event;
use Modules\Event\Entities\EventDate;
use Modules\Event\Entities\EventProperty;
use Modules\Event\Entities\Participant;
use Modules\Event\Repositories\EventRepository;
use Modules\Game\Entities\Game;
use Modules\Group\Entities\Format;
use Modules\Group\Entities\FormatType;
use Modules\Group\Entities\Group;
use Modules\Group\Entities\MatchSetting;
use Modules\Link\Entities\Link;
use Modules\Map\Entities\MapPool;
use Modules\Map\Entities\MapPoolOrder;
use Modules\Map\ManiaExchange\Repositories\MappackRepository;
use Modules\Map\ManiaExchange\Repositories\TMXMappackRepository;
use Modules\Match\Entities\MatchModel;
use Modules\Match\Entities\MatchEndCondition;
use Modules\Page\Entities\Page;
use Modules\Page\Entities\PageProperty;
use Modules\Play\Jobs\CachePlayEventsJob;
use Modules\Play\Services\HourlyEventService;
use Modules\Twitch\Services\TwitchService;
use Modules\User\Entities\User;

/**
 * Class ConsoleController
 * @package Modules\Console\Http\Controllers
 */
class ConsoleController extends Controller
{
    use RequiresConsoleAccess;

    /** @var DedicatedControllerService $dedicatedControllerService */
    private $dedicatedControllerService;

    /**
     * ConsoleController constructor.
     * @param DedicatedControllerService $dedicatedControllerService
     */
    public function __construct(DedicatedControllerService $dedicatedControllerService)
    {
        $this->dedicatedControllerService = $dedicatedControllerService;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getUserSettings()
    {
        /** @var User $user */
        $user = auth()->user();

        $pages = Page::where("user_id", $user->id)
            ->with("apiToken")
            ->get()
            ->toArray();

        foreach ($pages as $index => $page) {
            $events = DB::table(Event::TABLE_NAME)
                ->where("page_id", $page['id'])
                ->get(["id", "name"]);

            $mapPools = DB::table(MapPool::TABLE_NAME)
                ->whereIn("event_id", $events->map(function($event) {
                    return [$event->id];
                }))
                ->get(["id", "name"]);

            $pages[$index]["events"] = $events->toArray();
            $pages[$index]["mapPools"] = $mapPools->toArray();
        }

        return $pages;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Model|ApiToken
     */
    public function generateToken(Request $request)
    {
        $pageId = $request->input("pageId");

        return ApiToken::create([
            "user_id" => auth()->user()->id,
            "page_id" => $pageId,
            "token" => \Str::random(32)
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function startMatch(Request $request)
    {
        $this->verifyConsoleAccess();

        $matchId = $request->input("matchId");

        try {
            $this->dedicatedControllerService->startMatch($matchId);
        } catch (NoServerAvailable $exception) {
            return response()->json(["errors" => [
                "message" => "No server available."
            ]], Response::HTTP_SERVICE_UNAVAILABLE);
        } catch (\Throwable $exception) {
            return response()->json(["errors" => [
                "message" => "Could not start match."
            ]], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message" => "Success."
        ], Response::HTTP_OK);
    }

    public function getPageEvents($pageSlug)
    {
        $pageIds = DB::table(Page::TABLE_NAME)
            ->where("slug", $pageSlug)
            ->get(["id"])
            ->map(function ($page) {
                return $page->id;
            });

        return DB::table(Event::TABLE_NAME)
            ->whereIn("page_id", $pageIds)
            ->get([
                "id",
                "name",
                "status_id",
                "slug",
                "type"
            ]);
    }

    public function getUserEvents()
    {
        /** @var User $user */
        $user = auth()->user();

        $userPageIds = DB::table(Page::TABLE_NAME)
            ->where("user_id", $user->id)
            ->get(["id"])
            ->map(function ($page) {
                return $page->id;
            });

        return DB::table(Event::TABLE_NAME)
            ->whereIn("page_id", $userPageIds)
            ->get([
                "id",
                "name",
                "status_id",
                "slug",
                "type"
            ]);
    }

    public function getEvent($slug)
    {
        $event  = DB::table(Event::TABLE_NAME)->where("slug", $slug)->first();
        $groups = Group::where("event_id", $event->id)->with(['matches'])->get();

        $groups = $groups->filter(function (Group $group) {
            return $group->matches->filter(function (MatchModel $match) {
                /** @var DedicatedController $dedicatedController */
               $match->dedicatedController = DedicatedController::where("match_id", $match->id)->first();
               return $match;
            });
        });

        //TODO: add event data

        return [
            "event" => $event,
            "groups" => $groups
        ];
    }

    public function getConsoleAccess()
    {
        /** @var User $user */
        $user = auth()->user();

        $access = ConsoleAccess::where("user_id", $user->id)->get();

        /** @var ConsoleAccess $pass */
        foreach ($access as $pass) {
            if (!$pass->until->isPast()) {
                return $pass;
            }
        }

        return response()->json([
            "message" => "Not Authorized."
        ], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateWhitelist(Request $request)
    {
        $this->verifyConsoleAccess();

        $matchId = $request->input("matchId");

        try {
            $this->dedicatedControllerService->updateWhitelist($matchId);
        } catch (\Throwable $exception) {
            return $exception;
            return response()->json(["errors" => [
                "message" => "Could not update whitelist."
            ]], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message" => "Success."
        ], Response::HTTP_OK);
    }

    public function startMatchmakingMatch(Request $request)
    {
        $this->verifyConsoleAccess();

        $eventId = $request->input("eventId"); //Matchmaking Ladder
        $players = $request->input("players");

        $participants = Participant::where("event_id", $eventId)->get();

        $participantIds = [];
        foreach ($players as $player) {
            $participant = $participants->where('user_id', $player['user_id']);

            if (!$participant->count()) {
                $participant = new Participant();

                $participant->eventId = $eventId;
                $participant->userId  = $player['user_id'];
                $participant->pending = false;
                $participant->type    = Participant::TYPE_USER;

                $participant->save();
            } else {
                /** @var Participant $participant */
                $participant = $participant->first();
            }

            $participantIds[] = $participant->id;
        }

        /** @var MapPool $mapPool */
        $mapPool = MapPool::where("event_id", $eventId)->first();

        /** @var Format $format */
        $format = Format::where("event_id", $eventId)->first();

        /** @var Group $group */
        $group = Group::where("event_id", $eventId)->first();

        $match = new MatchModel();

        $match->name        = "Matchmaking Match";
        $match->date        = Carbon::now();
        $match->statusId    = MatchModel::STATUS_UPCOMING;
        $match->mapPoolId   = $mapPool->id;
        $match->groupId     = $group->id;

        $match->save();

        $match->formats()->sync([$format->id]);
        $match->participants()->sync($participantIds);

        //Select a random map
        /** @var TMXMappackRepository $mappackRepository */
        $mappackRepository = app(TMXMappackRepository::class);

        $tracks = $mappackRepository->getTracks($mapPool->mxId);
        $randomIndex = rand(0, count($tracks)-1);
        $selectedTrack = $tracks[$randomIndex];

        foreach ($tracks as $track) {
            $mapPoolOrder = new MapPoolOrder();

            $mapPoolOrder->matchId   = $match->id;
            $mapPoolOrder->mapPoolId = $mapPool->id;
            $mapPoolOrder->order     = $track->getId() == $selectedTrack->getId() ? 1 : 0;
            $mapPoolOrder->mxMapId   = $track->getId();

            $mapPoolOrder->save();
        }

        //Start server
        try {
            $this->dedicatedControllerService->startMatch($match->id);
        } catch (NoServerAvailable $exception) {
            return response()->json(["errors" => [
                "message" => "No server available."
            ]], Response::HTTP_SERVICE_UNAVAILABLE);
        } catch (\Throwable $exception) {
            return response()->json(["errors" => [
                "message" => "Could not start match."
            ]], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        //Send response
        return $match;
    }

    public function getAvailableServers()
    {
        $this->verifyConsoleAccess();

        $servers = DedicatedController::where("status_id", DedicatedController::OPEN)->get();

        $availableServersCount = $servers->count();

        /** @var DedicatedController $server */
        foreach ($servers as $server) {
            if ($server->matchId) {
                $availableServersCount--; //Reserved server or blocked
            }
        }

        return ["count" => $availableServersCount];
    }

    public function validateMatchmakingEvent(Request $request)
    {
        $eventId = $request->input("eventId");
        $userId  = $request->input("userId");

        //Has matchmaking property
        $properties = EventProperty::where("key", EventProperty::MATCHMAKING_LADDER)
            ->where("event_id", $eventId)
            ->count();

        if (!$properties) {
            return ["validation" => "fail", "message" => "Not a matchmaking ladder."];
        }

        //Is in progress
        /** @var EventDate $eventStart */
        $eventStart = EventDate::where("event_id", $eventId)
            ->where("name", EventDate::EVENT_START)
            ->first();

        /** @var EventDate $eventEnd */
        $eventEnd = EventDate::where("event_id", $eventId)
            ->where("name", EventDate::EVENT_END)
            ->first();

        $eventStart = new Carbon($eventStart->date);
        $eventEnd   = new Carbon($eventEnd->date);

        if (Carbon::now()->lte($eventStart)) {
            return ["validation" => "fail", "message" => "The event hasn't started yet."];
        }

        $eventOpen = Carbon::now()->gte($eventStart) && Carbon::now()->lte($eventEnd);
        if (!$eventOpen) {
            return ["validation" => "fail", "message" => "The event has ended."];
        }

        $isFolowerOnly = EventProperty::where("key", EventProperty::TWITCH_FOLLOWER_ONLY)
            ->where("event_id", $eventId)
            ->count();

        if ($isFolowerOnly) {
            /** @var TwitchService $twitchService */
            $twitchService = app(TwitchService::class);

            if (!$twitchService->isUserFollowingEventOwner($userId, $eventId)) {
                return ["validation" => "fail", "message" => "You are not following the event organizer on twitch."];
            }
        }

        $isSubscriberOnly = EventProperty::where("key", EventProperty::TWITCH_SUBSCRIBER_ONLY)
            ->where("event_id", $eventId)
            ->count();

        if ($isSubscriberOnly) {
            /** @var TwitchService $twitchService */
            $twitchService = app(TwitchService::class);

            if (!$twitchService->isUserSubscribedToEventOwner($userId, $eventId)) {
                return ["validation" => "fail", "message" => "You are not subscribed to the event organizer on twitch."];
            }
        }

        return ["validation" => "success"];
    }

    /**
     * @param Request $request
     * @return Event
     * @throws \Exception
     */
    public function createMatchmakingLadder(Request $request)
    {
        $this->verifyConsoleAccess();

        $pageId = $request->input("pageId");
        $eventName = $request->input("eventName");
        $eventAbout = $request->input("eventAbout");
        $eventCardImg = $request->input("eventCardImg");
        $mappackId = $request->input("mappackId");
        $followerOnly = $request->input("followerOnly");
        $subscriberOnly = $request->input("subscriberOnly");

        // Create event
        /** @var EventBuilder $eventBuilder */
        $eventBuilder = app(EventBuilder::class);

        /** @var EventBuilder $eventBuilder */
        $eventBuilder = $eventBuilder->prepare();

        $eventBuilder->setName($eventName)
            ->setAbout($eventAbout)
            ->setIsTeamEvent(false)
            ->setType("Matchmaking Ladder")
            ->setPageId($pageId)
            ->setPrivate(true)
            ->setRequiredGameAccount(true)
            ->setGameId(Game::TRACKMANIA_ID);

        /** @var EventRepository $eventRepository */
        $eventRepository = app(EventRepository::class);

        /** @var Event $event */
        $event = $eventRepository->create($eventBuilder);

        // Dates
        $date = $request->input("date");
        $startTime = $request->input("startTime");
        $endTime = $request->input("endTime");

        $startDate      = new Carbon($date." ".$startTime);
        $startDate->subHours(1);
        $eventStartDate = new EventDate();

        $eventStartDate->date         = $startDate;
        $eventStartDate->eventId      = $event->id;
        $eventStartDate->isStartDate  = true;
        $eventStartDate->isEndDate    = false;
        $eventStartDate->isActionDate = false;
        $eventStartDate->name         = EventDate::EVENT_START;

        $eventStartDate->save();

        $endDate      = new Carbon($date." ".$endTime);
        $endDate->subHours(1);
        $eventEndDate = new EventDate();

        $eventEndDate->date         = $endDate;
        $eventEndDate->eventId      = $event->id;
        $eventEndDate->isStartDate  = true;
        $eventEndDate->isEndDate    = false;
        $eventEndDate->isActionDate = false;
        $eventEndDate->name         = EventDate::EVENT_END;

        $eventEndDate->save();

        // Event card image
//        $link = new Link();
//
//        $link->eventId = $event->id;
//        $link->name = "event_card_img";
//        $link->url = $eventCardImg;
//        $link->pending = false;
//
//        $link->save();

        // Map pool
        $mapPool = new MapPool();

        $mapPool->mxId = $mappackId;
        $mapPool->eventId = $event->id;
        $mapPool->name = $eventName;

        $mapPool->save();

        // Format
        $format = new Format();

        $format->name = "1v1 Matchmaking";
        $format->eventId = $event->id;
        $format->typeId = FormatType::ROUNDS_VALUE;
        $format->inheritable = false;
        $format->areResultsAdditive = false;
        $format->isGameServerGuarded = false;
        $format->matchModifiableByParticipants = false;
        $format->requiresModeratorApproval = false;
        $format->description = "1v1 Matchmaking";

        $format->save();

        // Matchsettings
        $matchSetting = new MatchSetting();
        $matchSetting->key      = "S_PointsLimit";
        $matchSetting->value    = "7";
        $matchSetting->formatId = $format->id;
        $matchSetting->save();

        $matchSetting = new MatchSetting();
        $matchSetting->key      = "S_PointsRepartition";
        $matchSetting->value    = "1,0";
        $matchSetting->formatId = $format->id;
        $matchSetting->save();

        $matchSetting = new MatchSetting();
        $matchSetting->key      = "S_WarmUpNb";
        $matchSetting->value    = "1";
        $matchSetting->formatId = $format->id;
        $matchSetting->save();

        $matchSetting = new MatchSetting();
        $matchSetting->key      = "S_WarmUpDuration";
        $matchSetting->value    = "300";
        $matchSetting->formatId = $format->id;
        $matchSetting->save();

        $matchSetting = new MatchSetting();
        $matchSetting->key      = "S_RoundsPerMap";
        $matchSetting->value    = "999";
        $matchSetting->formatId = $format->id;
        $matchSetting->save();

        // Match end conditions
        $matchEndCondition = new MatchEndCondition();
        $matchEndCondition->minMapsPlayed = 1;
        $matchEndCondition->maxMapsPlayed = 1;
        $matchEndCondition->pointsReached = 7;
        $matchEndCondition->numberOfPlayersWithPointsReached = 1;
        $matchEndCondition->formatId = $format->id;
        $matchEndCondition->save();

        // Group
        $group = new Group();

        $group->eventId = $event->id;
        $group->name = "Matchmaking matches";
        $group->minSize = 1;
        $group->maxSize = 999;
        $group->type = Group::TYPE_GENERIC;
        $group->isTypeTree = false;
        $group->private = false;

        $group->save();

        // Properties
        EventProperty::create([
            "event_id"  => $event->id,
            "key"       => EventProperty::MATCHMAKING_LADDER,
            "value"     => true,
            "read_only" => true
        ]);
//        EventProperty::create([
//            "event_id"  => $event->id,
//            "key"       => EventProperty::RANKED_EVENT,
//            "value"     => true,
//            "read_only" => true
//        ]);

        if ($followerOnly) {
            EventProperty::create([
                "event_id"  => $event->id,
                "key"       => EventProperty::TWITCH_FOLLOWER_ONLY,
                "value"     => true,
                "read_only" => true
            ]);
        }

        if ($subscriberOnly) {
            EventProperty::create([
                "event_id"  => $event->id,
                "key"       => EventProperty::TWITCH_SUBSCRIBER_ONLY,
                "value"     => true,
                "read_only" => true
            ]);
        }

        CachePlayEventsJob::dispatch();

        return $event;
    }

    public function createShowdown(Request $request)
    {
        /** @var TMXMappackRepository $mappackRepository */
        $mappackRepository = app(TMXMappackRepository::class);
        $tracks = $mappackRepository->getTracks($request->input("mappackId"));
        $randomIndex = rand(0, count($tracks)-1);
        $map = $tracks[$randomIndex];

        $event = $this->createEvent(
            $request->input("eventName"),
            $request->input("eventAbout"),
            $request->input("pageId")
        );
        $date  = $this->createEventDate($event, $request->input("date"), $request->input("startTime"));

        $mapUrlProperty = $this->createEventProperty(
            $event->id,
            EventProperty::PLAY_MAP_URL,
            "https://trackmania.exchange/s/tr/".$map->getId()
        );
        $mapNameProperty = $this->createEventProperty(
            $event->id,
            EventProperty::PLAY_MAP_NAME,
            $map->getName()
        );
        $mapMxIdProperty = $this->createEventProperty(
            $event->id,
            EventProperty::PLAY_MAP_MX_ID,
            $map->getId()
        );
        $showdownProperty = $this->createEventProperty(
            $event->id,
            EventProperty::CUSTOM_SHOWDOWN,
            true
        );
        $adminProperty = $this->createEventProperty(
            $event->id,
            EventProperty::NON_PARTICIPANT,
            "erwinweber96"
        );

        $followerOnly = $request->input("followerOnly");
        $subscriberOnly = $request->input("subscriberOnly");

        if ($followerOnly) {
            $followerOnlyProperty = $this->createEventProperty(
                $event->id,
                EventProperty::TWITCH_FOLLOWER_ONLY,
                true
            );
        }

        if ($subscriberOnly) {
            $subscriberOnlyProperty = $this->createEventProperty(
                $event->id,
                EventProperty::TWITCH_SUBSCRIBER_ONLY,
                true
            );
        }

        $eventMapPool = $this->createMapPool($request->input("mappackId"), $event);

        CachePlayEventsJob::dispatch();

        return $event;
    }

    private function createEvent($eventName, $about, $pageId): Event
    {
        /** @var EventBuilder $eventBuilder */
        $eventBuilder = app(EventBuilder::class);

        $eventBuilder = $eventBuilder->setName($eventName)
            ->setStatusId(Event::STATUS_OPEN)
            ->setAbout($about)
            ->setGameId(Game::TRACKMANIA_ID)
            ->setIsTeamEvent(false)
            ->setPageId($pageId)
            ->setPrivate(true)
            ->setRegistrationOpen(true)
            ->setRequiredGameAccount(true)
            ->setType("Showdown");

        /** @var EventRepository $eventRepository */
        $eventRepository = app(EventRepository::class);

        /** @var Event $event */
        $event = $eventRepository->create($eventBuilder);

        return $event;
    }

    private function createEventDate(Event $event, $date, $startTime): EventDate
    {
        $eventDate = new EventDate();
        $eventDate->name            = EventDate::EVENT_START;
        $eventDate->isStartDate     = true;
        $eventDate->isEndDate       = true;
        $eventDate->isActionDate    = true;
        $eventDate->eventId         = $event->id;

        $date = new Carbon($date . " " . $startTime);
        $date = $date->subHour();

        $eventDate->date = $date->toDateTimeString();
        $eventDate->save();
        return $eventDate;
    }

    private function createEventProperty($eventId, $key, $value): EventProperty
    {
        $mapNameProperty = new EventProperty();
        $mapNameProperty->key       = $key;
        $mapNameProperty->value     = $value;
        $mapNameProperty->eventId   = $eventId;
        $mapNameProperty->readOnly  = true;
        $mapNameProperty->save();
        return $mapNameProperty;
    }

    private function createMapPool($mappackId, Event $event): MapPool
    {
        $mapPool = new MapPool();
        $mapPool->eventId = $event->id;
        $mapPool->name = $event->name;
        $mapPool->link = "https://trackmania.exchange/s/m/".$mappackId;
        $mapPool->mxId = $mappackId;
        $mapPool->save();
        return $mapPool;
    }
}
