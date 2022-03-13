<?php


namespace Modules\Match\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Match\Entities\MatchModel;
use Modules\Match\Entities\MatchAlert;
use Modules\Match\Services\MatchAlertService;
use Modules\User\Entities\User;

/**
 * Class MatchAlertController
 * @package Modules\Match\Http\Controllers
 */
class MatchAlertController
{
    /** @var MatchAlertService $service */
    public $service;

    /**
     * MatchAlertController constructor.
     * @param MatchAlertService $service
     */
    public function __construct(MatchAlertService $service)
    {
        $this->service = $service;
    }


    public function getMatchAlerts($matchId)
    {
        return $this->service->getMatchAlerts($matchId);
    }

    public function createMatchAlert(Request $request)
    {
        $matchId = $request->input("matchId");

        /** @var MatchModel $match */
        $match = MatchModel::where("id", $matchId)->first();

        /** @var User $user */
        $user = auth()->user();

        if ($user->id != $match->group->event->page->user->id) {
            return response()->json([
                "errors" => [
                    "message" => ["Not Authorized"]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $this->service->createMatchAlert(
            $matchId,
            $request->input("message"),
            $request->input("type"),
            $request->input("public")
        );
    }

    public function deleteMatchAlert($id)
    {
        /** @var MatchAlert $matchAlert */
        $matchAlert = MatchAlert::where("id", $id)->first();

        /** @var MatchModel $match */
        $match = MatchModel::where("id", $matchAlert->matchId)->first();

        /** @var User $user */
        $user = auth()->user();

        if ($user->id != $match->group->event->page->user->id) {
            return response()->json([
                "errors" => [
                    "message" => ["Not Authorized"]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $matchAlert->delete();
    }
}
