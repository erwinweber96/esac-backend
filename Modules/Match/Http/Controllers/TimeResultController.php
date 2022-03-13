<?php


namespace Modules\Match\Http\Controllers;


use Illuminate\Http\Request;
use Modules\Event\Entities\Participant;
use Modules\Match\Entities\TimeResult;

class TimeResultController
{
    public function createResult(Request $request)
    {
        $result = new TimeResult();
        $result->matchId        = $request->input("match_id");
        $result->time           = $request->input("time");
        $result->mapId          = $request->input("map_id");

        $nickname = $request->input("nickname");

        $participants = Participant::where("match_id", $result->matchId)->get();
        $participant = $participants->filter(function (Participant $participant) use ($nickname) {
            return $participant->user->tmNickname == $nickname;
        });

        /** @var Participant $player */
        $player = $participant->first();

        $result->participantId = $player->id;

        $result->save();
        return $result;
    }

    public function getTimeResults($matchId)
    {
        return TimeResult::where("match_id", $matchId)->get();
    }
}
