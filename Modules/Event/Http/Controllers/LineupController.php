<?php


namespace Modules\Event\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Event\Entities\EventProperty;
use Modules\Event\Entities\Lineup;
use Modules\Event\Entities\Participant;
use Modules\Page\Entities\PageMember;
use Modules\User\Entities\User;

/**
 * Class LineupController
 * @package Modules\Event\Http\Controllers
 */
class LineupController
{
    public function get($participantId)
    {
        return Lineup::where("participant_id", $participantId)
            ->with(["user"])
            ->get();
    }

    public function save(Request $request)
    {
        $participantId = $request->input("participantId");

        /** @var Participant $participant */
        $participant = Participant::where("id", $participantId)->first();

        /** @var User $user */
        $user = auth()->user();

        if ($participant->page->user->id != $user->id) {
            return response()->json([
                "errors" => [
                    "message" => ["Not Authorized."]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        $lineupChangeProperty = $participant->event->properties->filter(function (EventProperty $eventProperty) {
           if ($eventProperty->key == EventProperty::LINEUP_CHANGE_ALLOWED) {
               return true;
           }

           return false;
        });

        if ($lineupChangeProperty->count()) {
            /** @var EventProperty $lineupChangeProperty */
            $lineupChangeProperty = $lineupChangeProperty->first();

            if (!$lineupChangeProperty->value) {
                return response()->json([
                    "errors" => [
                        "message" => ["Lineup change is currently not allowed."]
                    ]
                ], Response::HTTP_UNAUTHORIZED);
            }
        }

        Lineup::where("participant_id", $participantId)->delete();

        $members = $request->input("members");
        foreach ($members as $member) {
            /** @var PageMember $pageMember */
            $pageMember = PageMember::where("id", $member)->first();

            Lineup::create([
                "participant_id" => $participantId,
                "page_member_id" => $member,
                "user_id"        => $pageMember->userId
            ]);
        }
    }
}
