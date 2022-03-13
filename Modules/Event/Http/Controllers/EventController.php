<?php

namespace Modules\Event\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Event\Builders\EventBuilder;
use Modules\Event\Builders\EventModeratorBuilder;
use Modules\Event\Entities\Event;
use Modules\Event\Entities\EventDate;
use Modules\Event\Entities\EventModerator;
use Modules\Event\Entities\EventModeratorRole;
use Modules\Event\Entities\EventProperty;
use Modules\Event\Entities\Participant;
use Modules\Event\Events\EventUpdated;
use Modules\Event\Exceptions\DiscordNotSet;
use Modules\Event\Exceptions\GameAccountNotLinked;
use Modules\Event\Exceptions\NonTwitchFollower;
use Modules\Event\Exceptions\NonTwitchSubscriber;
use Modules\Event\Exceptions\NoSlotAvailable;
use Modules\Event\Exceptions\ParticipantAlreadyRegistered;
use Modules\Event\Exceptions\RegistrationClosed;
use Modules\Event\Exceptions\TwitchNotSet;
use Modules\Event\Factories\ParticipantValidatorFactory;
use Modules\Event\Http\Requests\CreateEventRequest;
use Modules\Event\Http\Requests\CreateModeratorRequest;
use Modules\Event\Http\Requests\GetEventParticipantsRequest;
use Modules\Event\Http\Requests\RegisterParticipantRequest;
use Modules\Event\Http\Requests\RemoveParticipantRequest;
use Modules\Event\Http\Requests\UpdateEventRequest;
use Modules\Event\Http\Requests\UpdateEventStatusRequest;
use Modules\Event\Http\Requests\WithdrawParticipantRequest;
use Modules\Event\Jobs\BuildParticipant;
use Modules\Event\Jobs\GiveCreatorEventRoles;
use Modules\Event\Jobs\SendParticipantRegisteredEventToDiscordWebhook;
use Modules\Event\Repositories\EventRepository;
use Modules\Event\Repositories\ParticipantRepository;
use Modules\Event\Services\EventRegistrationService;
use Modules\Event\Validators\GameAccountValidator;
use Modules\Event\Validators\ParticipantValidator;
use Modules\Game\Entities\Game;
use Modules\GameServer\Entities\GameServer;
use Modules\Match\Entities\LiveStream;
use Modules\Match\Entities\MatchResult;
use Modules\Match\Entities\Vod;
use Modules\Page\Entities\Page;
use Modules\Page\Entities\PageMember;
use Modules\Page\Entities\PageMemberRole;
use Modules\Page\Repositories\PageRepository;
use Modules\User\Entities\User;
use Illuminate\Support\Facades\DB;
use Modules\Group\Entities\Group;
use Modules\Match\Entities\MatchModel;

/**
 * Class EventController
 * @package Modules\Event\Http\Controllers
 */
class EventController extends Controller
{
    /** @var EventRepository $eventRepository */
    private $eventRepository;

    /** @var ParticipantRepository $participantRepository */
    private $participantRepository;

    /** @var BuildParticipant $buildParticipant */
    private $buildParticipant;

    /** @var PageRepository $pageRepository */
    private $pageRepository;

    /** @var EventRegistrationService $eventRegistrationService */
    private $eventRegistrationService;

    /**
     * EventController constructor.
     * @param EventRepository $eventRepository
     * @param ParticipantRepository $participantRepository
     * @param BuildParticipant $buildParticipant
     * @param PageRepository $pageRepository
     * @param EventRegistrationService $eventRegistrationService
     */
    public function __construct(
        EventRepository $eventRepository,
        ParticipantRepository $participantRepository,
        BuildParticipant $buildParticipant,
        PageRepository $pageRepository,
        EventRegistrationService $eventRegistrationService
    ) {
        $this->eventRepository = $eventRepository;
        $this->participantRepository = $participantRepository;
        $this->buildParticipant = $buildParticipant;
        $this->pageRepository = $pageRepository;
        $this->eventRegistrationService = $eventRegistrationService;
    }


    /**
     * @param CreateEventRequest $request
     * @return JsonResponse
     */
    public function create(CreateEventRequest $request)
    {
        //TODO: alt tip de guard

        $request->validated();

        $pageId = $request->input("pageId");

        /** @var Page $page */
        $page = $this->pageRepository->show($pageId);

        /** @var User $user */
        $user = auth()->user();

        if ($user->cannot(PageMemberRole::CREATE_EVENTS, [$page])) {
            return response()->json([
                "errors" => [
                    "message" => ["Not Authorized"]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        /** @var EventBuilder $eventBuilder */
        $eventBuilder = (new EventBuilder())->prepare();

        $eventBuilder
            ->setType($request->input("type"))
            ->setName($request->input("name"))
            ->setAbout($request->input("about"))
            ->setIsTeamEvent($request->input("isTeamEvent"))
            ->setPageId($request->input("pageId"))
            ->setGameId($request->input("gameId"));

        /** @var Event $event */
        $event = $this->eventRepository->create($eventBuilder);

        $giveRoles = new GiveCreatorEventRoles();
        $giveRoles->execute($event);

        $date = $request->input("date");
        $time = $request->input("time");

        $date      = new Carbon($date." ".$time);
        $eventDate = new EventDate();

        $eventDate->date         = $date;
        $eventDate->eventId      = $event->id;
        $eventDate->isStartDate  = true;
        $eventDate->isEndDate    = false;
        $eventDate->isActionDate = false;
        $eventDate->name         = EventDate::EVENT_START;

        $eventDate->save();
        return response()->json($event, Response::HTTP_OK);
    }

    public function get($slug)
    {
        return response()->json(
            $this->eventRepository->findBySlug($slug),
            Response::HTTP_OK
        );
    }

    public function register(RegisterParticipantRequest $request)
    {
        //TODO: only requested participant should be allowed to register in his name/page

        $request->validated();

        /** @var Event $event */
        $event = $this->eventRepository->show($request->input("eventId"));

        $validator = (new ParticipantValidatorFactory())->make($event);

        try {
            $validator->validate($request);
        } catch (\Exception $exception) {
            return response()->json([
                "errors" =>  $exception->getMessage()
            ], $exception->getCode());
        }

        try {
            $this->eventRegistrationService
                ->validateRegistrationRequirements($event, $request->input("participantId"));
        } catch (DiscordNotSet $e) {
            return response()->json([
                "errors" => ["message" => "Please set your discord nickname and id before registering to this event."]
            ], Response::HTTP_BAD_REQUEST);
        } catch (GameAccountNotLinked $e) {
            return response()->json([
                "errors" => ["message" => $e->getMessage()]
            ], Response::HTTP_BAD_REQUEST);
        } catch (NoSlotAvailable $e) {
            return response()->json([
                "errors" => ["message" => "No slot available."]
            ], Response::HTTP_BAD_REQUEST);
        } catch (ParticipantAlreadyRegistered $e) {
            return response()->json([
                "errors" => ["message" => "You are already registered."]
            ], Response::HTTP_BAD_REQUEST);
        } catch (RegistrationClosed $e) {
            return response()->json([
                "errors" => ["message" => "Registration is closed."]
            ], Response::HTTP_BAD_REQUEST);
        } catch (TwitchNotSet $e) {
            return response()->json([
                "errors" => ["message" => "You need to link your twitch account in the settings."]
            ], Response::HTTP_BAD_REQUEST);
        } catch (NonTwitchFollower $e) {
            return response()->json([
                "errors" => ["message" => "You are not following the event organizer on twitch."]
            ], Response::HTTP_BAD_REQUEST);
        } catch (NonTwitchSubscriber $e) {
            return response()->json([
                "errors" => ["message" => "You are not subscribed to the event organizer on twitch."]
            ], Response::HTTP_BAD_REQUEST);
        }

        $builder     = $this->buildParticipant->execute($request, $event);
        $participant = $this->participantRepository->create($builder);

        SendParticipantRegisteredEventToDiscordWebhook::dispatch($participant, $event);

        return response()->json($participant,Response::HTTP_OK);
    }

    public function withdraw(WithdrawParticipantRequest $request)
    {
        $request->validated();

        /** @var Event $event */
        $event = $this->eventRepository->show($request->eventId);

        if ($event->statusId == Event::STATUS_LIVE) {
            return response()->json([
                "errors" => ["message" => "You cannot withdraw while the event is live."]
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($event->statusId == Event::STATUS_ENDED) {
            return response()->json([
                "errors" => ["message" => "You cannot withdraw after the event has ended."]
            ], Response::HTTP_BAD_REQUEST);
        }

        $participantFound = false;

        if ($event->isTeamEvent) {
            foreach ($event->participants as $participant) {
                if ($participant->page->user->id == $request->userId) {
                    $participantFound = true;

                    try {
                        $this->participantRepository->delete($participant->id);
                    } catch (\Throwable $exception) {
                        return response()->json([
                            "errors" => ["message" => "Something went wrong. Could not withdraw from event."]
                        ], Response::HTTP_BAD_REQUEST);
                    }
                }
            }
        } else {
            /** @var Participant $participant */
            $participant = Participant::where("event_id", $request->eventId)
                ->where("user_id", $request->userId)
                ->first();

            if ($participant) {
                $participantFound = true;

                try {
                    $this->participantRepository->delete($participant->id);
                } catch (\Throwable $exception) {
                    return response()->json([
                        "errors" => ["message" => "Something went wrong. Could not withdraw from event."]
                    ], Response::HTTP_BAD_REQUEST);
                }
            }
        }

        if (!$participantFound) {
            return response()->json([
                "errors" => ["message" => "You are not registered."]
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            "message" => "Successfully withdrawn from event!"
        ], Response::HTTP_OK);
    }

    public function removeParticipant(RemoveParticipantRequest $request)
    {
        $request->validated();

        /** @var User $user */
        $user = auth()->user();

        /** @var Participant $participant */
        $participant = Participant::where("id", $request->participantId)->first();

        if (!$participant) {
            return response()->json([
                "errors" => ["message" => "Participant not found."]
            ], Response::HTTP_NOT_FOUND);
        }

        if ($user->cannot(EventModeratorRole::REMOVE_PARTICIPANTS, [$participant->event])) {
            return response()->json([
                "errors" => [
                    "message" => ["Not Authorized"]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $this->participantRepository->delete($request->participantId);
        } catch (\Throwable $exception) {
            return response()->json([
                "errors" => ["message" => "Something went wrong. Could not remove participant from event."]
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            "message" => "Successfully removed participant from event."
        ], Response::HTTP_OK);
    }

    public function approveParticipant(RemoveParticipantRequest $request)
    {
        $request->validated();

        /** @var User $user */
        $user = auth()->user();

        /** @var Participant $participant */
        $participant = Participant::where("id", $request->participantId)->first();

        if (!$participant) {
            return response()->json([
                "errors" => ["message" => "Participant not found."]
            ], Response::HTTP_NOT_FOUND);
        }

        if ($user->cannot(EventModeratorRole::REMOVE_PARTICIPANTS, [$participant->event])) {
            return response()->json([
                "errors" => [
                    "message" => ["Not Authorized"]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $participant->pending = false;
            $participant->save();
        } catch (\Throwable $exception) {
            return response()->json([
                "errors" => ["message" => "Something went wrong. Could not approve Participant."]
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            "message" => "Successfully removed participant from event."
        ], Response::HTTP_OK);
    }

    public function getEvents()
    {
        $events = DB::table(Event::TABLE_NAME)
            ->where("private", false)
            ->orderBy("status_id", "asc")
            ->get();

        $pageIds = $events->map(function ($event) {
            return $event->page_id;
        });
        $pageIds = $pageIds->toArray();

        $pages = DB::table(Page::TABLE_NAME)
            ->whereIn("id", $pageIds)
            ->get(["name", "slug", "id"]);

        $events = $events->map(function ($event) use ($pages){
            $event->page = $pages->where("id", $event->page_id)->first();
            $event->event_dates = DB::table(EventDate::TABLE_NAME)->where("event_id", $event->id)->get();
            return $event;
        });

        $sorted = $events->sort(function ($a, $b) {
           $eventStartA = $a->event_dates->filter(function ($date) {
                return $date->name == EventDate::EVENT_START;
           });

            $eventStartB = $b->event_dates->filter(function ($date) {
                return $date->name == EventDate::EVENT_START;
            });

            $eventStartA = new Carbon($eventStartA->first()->date);
            $eventStartB = new Carbon($eventStartB->first()->date);

            if ($eventStartA->gte($eventStartB)) {
                return -1;
            }

            return 1;
        });

        $array = [];
        foreach ($sorted as $object) {
            $array[] = $object;
        }

        return $array;
    }

    public function getFeaturedEvents()
    {
        $events = DB::table(Event::TABLE_NAME)
            ->where('private', false)
            ->where('events.status_id', '!=', Event::STATUS_ENDED)
            ->orderBy('events.status_id', 'desc')
            ->orderBy("events.created_at", "asc")
            ->get();

        $data = [];
        foreach ($events as $event) {
            $page = DB::table(Page::TABLE_NAME)
                ->where('id', $event->page_id)
                ->first();
            unset($page->invite_token);

            $eventDates = DB::table(EventDate::TABLE_NAME)
                ->where("event_id", $event->id)
                ->get(['name', 'date']);

            $event->page = $page;
            $event->dates = $eventDates;

            $data[] = $event;
        }

        return $data;
    }

    public function updateStatus(UpdateEventStatusRequest $request)
    {
        $request->validated();

        /** @var User $user */
        $user = auth()->user();

        /** @var Event $event */
        $event = Event::where("id", $request->eventId)->first();

        $admin = false;
        if ($user->id == 15) {
            $admin = true;
        }

        if ($user->cannot(EventModeratorRole::CHANGE_EVENT_STATUS, [$event]) && !$admin) {
            return response()->json([
                "errors" => [
                    "message" => ["Not Authorized"]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        $event->statusId = $request->statusId;

        try {
            $event->save();
        } catch (\Throwable $exception) {
            return response()->json([
                "errors" => ["message" => "Something went wrong. Could not update event status."]
            ], Response::HTTP_BAD_REQUEST);
        }

//        /** @var EventBuilder $eventBuilder */
//        $eventBuilder = (new EventBuilder())->prepare();
//
//        $builder = $eventBuilder->setStatusId($request->statusId);
//        $updated = $this->eventRepository->update($builder, $request->eventId);

        return response()->json([
            "message" => "Successfully updated event status."
        ], Response::HTTP_OK);
    }

    public function updateEvent(UpdateEventRequest $request, $slug)
    {
        $request->validated();

        /** @var Event $event */
        $event = $this->eventRepository->findBySlug($slug);

        /** @var User $user */
        $user = auth()->user();

        $admin = false;
        if ($user->id == 15) {
            $admin = true;
        }

        if ($user->id != $event->page->user->id && !$admin) {
            return response()->json([
                "errors" => [
                    "message" => ["Not Authorized"]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        /** @var EventBuilder $eventBuilder */
        $eventBuilder = (new EventBuilder())->prepare();

        $eventBuilder->setAbout($request->about)
            ->setGameId($request->gameId)
            ->setIsTeamEvent($request->isTeamEvent)
            ->setType($request->type)
            ->setPrivate($request->isPrivate)
            ->setRegistrationOpen($request->registrationOpen)
            ->setRequiredGameAccount($request->requiredGameAccount)
            ->setName($request->name, false);

        $updated = $event->update($eventBuilder->build());

        EventProperty::where("event_id", $event->id)
            ->where("key", EventProperty::LINEUP_CHANGE_ALLOWED)
            ->delete();

        EventProperty::create([
            "event_id"  => $event->id,
            "key"       => EventProperty::LINEUP_CHANGE_ALLOWED,
            "value"     => $request->isLineupChangeAllowed,
            "read_only" => true
        ]);

        EventProperty::where("event_id", $event->id)
            ->where("key", EventProperty::DISCORD_REQUIRED)
            ->delete();

        EventProperty::create([
            "event_id"  => $event->id,
            "key"       => EventProperty::DISCORD_REQUIRED,
            "value"     => $request->discordRequired,
            "read_only" => true
        ]);

        EventProperty::where("event_id", $event->id)
            ->where("key", EventProperty::PENDING_REGISTRATION)
            ->delete();

        EventProperty::create([
            "event_id"  => $event->id,
            "key"       => EventProperty::PENDING_REGISTRATION,
            "value"     => $request->pendingRegistration,
            "read_only" => true
        ]);

        if (!$updated) {
            return response()->json([
                "errors" => [
                    "message" => ["Something went wrong."]
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message" => "Successfully updated event."
        ], Response::HTTP_OK);
    }

    public function createModerator(CreateModeratorRequest $request, $slug)
    {
        $request->validated();

        /** @var Event $event */
        $event = $this->eventRepository->findBySlug($slug);

        /** @var User $user */
        $user = auth()->user();

        $admin = false;
        if ($user->id == 15) {
            $admin = true;
        }

        if ($user->id != $event->page->user->id && !$admin) {
            return response()->json([
                "errors" => [
                    "message" => ["Not Authorized"]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        /** @var PageMember $member */
        $member = PageMember::where("id", $request->memberId)->first();

        try {
            return EventModerator::create([
                "user_id" => $member->user->id,
                "event_id" => $event->id
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                "errors" => [
                    "message" => ["Something went wrong."]
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateModeratorRoles(Request $request, $slug, $moderatorId)
    {
        $roles = $request->toArray();

        /** @var Event $event */
        $event = $this->eventRepository->findBySlug($slug);

        if (!$event) {
            return response()->json([
                "error" => "Could not find event by given slug."
            ], Response::HTTP_NOT_FOUND);
        }

        /** @var User $user */
        $user = auth()->user();

        $admin = false;
        if ($user->id == 15) {
            $admin = true;
        }

        if ($user->id != $event->page->user->id && !$admin) {
            return response()->json([
                "error" => "Not authorized to assign roles."
            ], Response::HTTP_UNAUTHORIZED);
        }

        foreach ($roles as $roleName => $allowed) {
            if (!in_array($roleName, EventModeratorRole::ROLES)) {
                continue;
            }

            if ($allowed) {
                $exists = EventModeratorRole::where("event_id", $event->id)
                    ->where("moderator_id", $moderatorId)
                    ->where("name", $roleName)
                    ->get();

                if (!$exists->count()) {
                    EventModeratorRole::create([
                        "event_id" => $event->id,
                        "moderator_id" => $moderatorId,
                        "name" => $roleName,
                    ]);
                }
            } else {
                EventModeratorRole::where("event_id", $event->id)
                    ->where("moderator_id", $moderatorId)
                    ->where("name", $roleName)
                    ->delete();
            }
        }

        return response()->json(["message" => "Successfully updated roles.", Response::HTTP_OK]);
    }

    /**
     * @param GetEventParticipantsRequest $request
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getEventParticipants(GetEventParticipantsRequest $request)
    {
        return Participant::where("event_id", $request->eventId)
            ->with("user")
            ->with("user.maniaplanet")
            ->with("page")
            ->get();
    }

    public function preRegister(RegisterParticipantRequest $request)
    {
        $requiresConsent = EventProperty::where("event_id", $request->eventId)
            ->where("key", EventProperty::FRANCE_BASED)
            ->count();

        if ($requiresConsent) {
            return response()->json(["consent" => "france_based"], Response::HTTP_OK);
        }

        $isWeeklyEvent = EventProperty::where("event_id", $request->eventId)
            ->where("key", EventProperty::WEEKLY_EVENT)
            ->count();

        if ($isWeeklyEvent) {
            return response()->json(["consent" => "weekly_3v3_event"], Response::HTTP_OK);
        }

        return response()->json(["consent" => false], Response::HTTP_OK);
    }

    public function getEventsWithPropertyKey($key)
    {
        $eventProperties = EventProperty::where("key", $key)->get()
            ->map(function (EventProperty $eventProperty) {
                return $eventProperty->eventId;
            });
        return Event::whereIn("id", $eventProperties->toArray())
            ->with("participants")
            ->get();
    }

    public function addNonUser(Request $request)
    {
        $eventId = $request->input("eventId");

        $event = DB::table(Event::TABLE_NAME)
            ->where('id', $eventId)
            ->first(['page_id']);

        $page = DB::table(Page::TABLE_NAME)
            ->where('id', $event->page_id)
            ->first(['user_id']);

        /** @var User $user */
        $user = auth()->user();

        $admin = false;
        if ($user->id == 15) {
            $admin = true;
        }

        if ($user->id != $page->user_id && !$admin) {
            return response()->json([
                "error" => "Not authorized."
            ], Response::HTTP_UNAUTHORIZED);
        }

        $participant = new Participant();

        $participant->eventId = $eventId;
        $participant->name    = $request->input("name");
        $participant->type    = Participant::TYPE_NON_USER;
        $participant->pending = false;

        $participant->save();
        return $participant;
    }

    public function editEventStartDate(Request $request)
    {
        $eventId = $request->input("eventId");

        $event = DB::table(Event::TABLE_NAME)
            ->where("id", $eventId)
            ->first(["page_id"]);

        $page = DB::table(Page::TABLE_NAME)
            ->where("id", $event->page_id)
            ->first(['user_id']);

        /** @var User $user */
        $user = auth()->user();

        $admin = false;
        if ($user->id == 15) {
            $admin = true;
        }

        if ($page->user_id != $user->id && !$admin) {
            return response()->json([], Response::HTTP_UNAUTHORIZED);
        }

        $date    = $request->input("date");
        $time    = $request->input("time");
        $date     = new Carbon($date." ".$time);

        /** @var EventDate $eventDate */
        $eventDate = EventDate::where("event_id", $eventId)
            ->where("name", EventDate::EVENT_START)
            ->first();

        $eventDate->date = $date;
        $eventDate->save();
        return $eventDate;
    }

    public function getPendingSubmissions($slug)
    {
        $event = DB::table(Event::TABLE_NAME)->where("slug", $slug)->first(["id"]);
        $eventId = $event->id;

        $groups = DB::table(Group::TABLE_NAME)->where("event_id", $eventId)->get(["id"]);

        $matchIds = [];
        $allMatches = [];
        foreach($groups as $group) {
            $matches = DB::table(MatchModel::TABLE_NAME)
                ->where("group_id", $group->id)
                ->get(["id", "name"]);
            $allMatches = array_merge($allMatches, $matches->toArray());
            foreach ($matches as $match) {
                $matchIds[] = $match->id;
            }
        }

        $matchResults = MatchResult::whereIn("match_id", $matchIds)
            ->where("pending", true)
            ->get();

        $gamerServerMatches = DB::table("game_server_match")
            ->whereIn("match_id", $matchIds)
            ->get();

        $gamerServerMatch = $gamerServerMatches->map(function ($entry) {
            return $entry->game_server_id;
        });

        $gameServers = GameServer::whereIn("id", $gamerServerMatch->toArray())
            ->where("pending", true)
            ->get();

        $gameServers = $gameServers->map(function ($gameServer) use ($gamerServerMatches){
             $gameServerMatch = $gamerServerMatches->where('game_server_id', $gameServer->id)->first();
             $gameServer['match_id'] = $gameServerMatch->match_id;
             return $gameServer;
        });

        $streams = LiveStream::whereIn("match_id", $matchIds)->get();
        $streams = $streams->filter(function (LiveStream $liveStream) {
            return $liveStream->link->pending == true;
        });

        $vods = Vod::whereIn("match_id", $matchIds)->get();
        $vods = $vods->filter(function (Vod $vod) {
            return $vod->link->pending == true;
        });

        return [
            "matchResults" => $matchResults,
            "gameServers" => $gameServers,
            "streams" =>  $streams,
            "vods" => $vods,
            "matches" => array_values($allMatches)
        ];
    }
}
