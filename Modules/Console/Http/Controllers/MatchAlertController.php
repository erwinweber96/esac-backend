<?php


namespace Modules\Console\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Match\Entities\MatchModel;
use Modules\Match\Entities\MatchAlert;
use Modules\Match\Services\MatchAlertService;
use Modules\User\Entities\User;

/**
 * Class MatchAlertController
 * @package Modules\Console\Http\Controllers
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
        return $this->service->createMatchAlert(
            $request->input("matchId"),
            $request->input("message"),
            $request->input("type"),
            true
        );
    }

    public function deleteMatchAlert($id)
    {
        /** @var MatchAlert $matchAlert */
        $matchAlert = MatchAlert::where("id", $id)->first();
        return $matchAlert->delete();
    }
}
