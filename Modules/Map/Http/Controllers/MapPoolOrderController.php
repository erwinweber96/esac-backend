<?php


namespace Modules\Map\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Modules\Map\Entities\MapPoolOrder;
use Modules\Match\Entities\MatchModel;
use Modules\User\Entities\User;

class MapPoolOrderController
{
    /**
     * @param $matchId
     * @return MapPoolOrder[]|Collection
     */
    public function getByMatchId($matchId)
    {
        return MapPoolOrder::where("match_id", $matchId)
            ->orderBy("order", "asc")
            ->get();
    }

    public function save(Request $mapPoolOrders)
    {
        $matchId = $mapPoolOrders->input("matchId");

        /** @var MatchModel $match */
        $match = MatchModel::where("id", $matchId)->first();

        /** @var User $user */
        $user = auth()->user();

        if ($user->id !== $match->group->event->page->user->id) {
            return response()->json(["errors" => [
                "message" => "Not Authorized."
            ]], Response::HTTP_UNAUTHORIZED);
        }

        $orders = $mapPoolOrders->input("orders");

        foreach ($orders as $mapPoolOrderData) {
            if (!isset($mapPoolOrderData['order'])) {
                continue;
            }

            if (isset($mapPoolOrderData['id'])) {
                $id = $mapPoolOrderData['id'];

                /** @var MapPoolOrder $mapPoolOrder */
                $mapPoolOrder = MapPoolOrder::where("id", $id)->first();
            } else {
                $mapPoolOrder = MapPoolOrder::where("mx_map_id", $mapPoolOrderData['mxMapId'])
                    ->where("match_id", $matchId)
                    ->first();
                if (!$mapPoolOrder) {
                    $mapPoolOrder = new MapPoolOrder();
                }
            }

            $mapPoolOrder->matchId      = $matchId;
            $mapPoolOrder->mapPoolId    = $mapPoolOrderData['mapPoolId'];
            $mapPoolOrder->mxMapId      = $mapPoolOrderData['mxMapId'];
            $mapPoolOrder->order        = $mapPoolOrderData['order'];

            $mapPoolOrder->save();
        }

        return response()->json(["message" => "Success"], Response::HTTP_OK);
    }

    public function delete($matchId) {
        /** @var MatchModel $match */
        $match = MatchModel::where("id", $matchId)->first();

        /** @var User $user */
        $user = auth()->user();

        if ($user->id !== $match->group->event->page->user->id) {
            return response()->json(["errors" => [
                "message" => "Not Authorized."
            ]], Response::HTTP_UNAUTHORIZED);
        }

        return MapPoolOrder::where("match_id", $matchId)->delete();
    }
}
