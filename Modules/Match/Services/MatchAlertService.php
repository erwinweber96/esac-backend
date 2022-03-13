<?php


namespace Modules\Match\Services;


use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Modules\Match\Entities\MatchModel;
use Modules\Match\Entities\MatchAlert;

/**
 * Class MatchAlertService
 * @package Modules\Match\Services
 */
class MatchAlertService
{
    /**
     * @param $matchId
     * @param false $withRights
     * @return MatchAlert[]|Collection
     *
     * TODO: in the future maybe private alerts might be useful
     */
    public function getMatchAlerts($matchId, $withRights = false)
    {
        $query = MatchAlert::where("match_id", $matchId);
        return $query->get();
    }

    public function createMatchAlert($matchId, $message, $type, $public)
    {
        $matchAlert = new MatchAlert();

        $matchAlert->matchId = $matchId;
        $matchAlert->message = $message;
        $matchAlert->type = $type;
        $matchAlert->public = $public;

        $matchAlert->save();
        return $matchAlert;
    }
}
