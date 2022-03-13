<?php


namespace Modules\Console\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Event\Entities\Event;
use Modules\Event\Entities\Lineup;
use Modules\Event\Entities\Participant;
use Modules\Event\Exceptions\DiscordNotSet;
use Modules\Event\Exceptions\GameAccountNotLinked;
use Modules\Event\Exceptions\NoSlotAvailable;
use Modules\Event\Exceptions\ParticipantAlreadyRegistered;
use Modules\Event\Exceptions\RegistrationClosed;
use Modules\Event\Jobs\BuildParticipant;
use Modules\Event\Repositories\ParticipantRepository;
use Modules\Event\Services\EventRegistrationService;
use Modules\Page\Entities\PageMember;

/**
 * Class WeeklyTeamEventController
 * @package Modules\Console\Http\Controllers
 */
class WeeklyTeamEventController
{
    /** @var EventRegistrationService $eventRegistrationService */
    private $eventRegistrationService;

    /** @var BuildParticipant $buildParticipant */
    private $buildParticipant;

    /** @var ParticipantRepository $participantRepository */
    private $participantRepository;

    /**
     * WeeklyTeamEventController constructor.
     * @param EventRegistrationService $eventRegistrationService
     * @param BuildParticipant $buildParticipant
     * @param ParticipantRepository $participantRepository
     */
    public function __construct(EventRegistrationService $eventRegistrationService, BuildParticipant $buildParticipant, ParticipantRepository $participantRepository)
    {
        $this->eventRegistrationService = $eventRegistrationService;
        $this->buildParticipant = $buildParticipant;
        $this->participantRepository = $participantRepository;
    }


    public function wizardRegistration(Request $request)
    {
        $pageId  = $request->input("pageId");
        $eventId = $request->input("eventId");
        $members = $request->input("members");

        if (count($members) != 3) {
            return response()->json([
                "errors" => ["message" => "Please select exactly 3 players for your lineup."]
            ], Response::HTTP_BAD_REQUEST);
        }

        $errors  = [];
        foreach ($members as $member) {
            /** @var PageMember $pageMember */
            $pageMember = PageMember::where("id", $member)->first();

            $tmNickname = $pageMember->user->tmNickname;
            if (!$tmNickname) {
                $errors[] = ["message" => $pageMember->user->nickname . " has not linked their Trackmania account."];
            }
        }

        if (count($errors)) {
            return response()->json([
                "errors" => $errors
            ], Response::HTTP_BAD_REQUEST);
        }

        /** @var Event $event */
        $event = Event::where("id", $eventId)->first();

        $participant = Participant::where("page_id", $pageId)
            ->where("event_id", $eventId)
            ->get();

        if ($participant->count()) {
            return response()->json([
                "errors" => ["message" => "You are already registered."]
            ], Response::HTTP_BAD_REQUEST);
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
        }

        $participant = new Participant();

        $participant->pageId    = $pageId;
        $participant->eventId   = $eventId;
        $participant->type      = Participant::TYPE_TEAM;
        $participant->pending   = false;

        $participant->save();

        foreach ($members as $member) {
            /** @var PageMember $pageMember */
            $pageMember = PageMember::where("id", $member)->first();

            Lineup::create([
                "participant_id" => $participant->id,
                "page_member_id" => $member,
                "user_id"        => $pageMember->userId
            ]);
        }

        return response()->json($participant,Response::HTTP_OK);
    }
}
