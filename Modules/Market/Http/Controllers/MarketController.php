<?php

namespace Modules\Market\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Market\Entities\Badge;
use Modules\User\Entities\CoinTransaction;
use Modules\User\Entities\User;

/**
 * Class MarketController
 * @package Modules\Market\Http\Controllers
 */
class MarketController extends Controller
{
    public function getMarketBadges()
    {
        return Badge::where("is_visible", "=", true)
            ->where("is_purchasable", '=', true)
            ->get();
    }

    public function purchaseBadge($badgeId)
    {
        /** @var User $user */
        $user = auth()->user();

        //check if authenticated
        if (!$user) {
            return response()->json(["errors" => [
                "message" => "Not Authenticated."
            ]], Response::HTTP_BAD_REQUEST);
        }

        //check if purchasable
        /** @var Badge $badge */
        $badge = Badge::where("id", $badgeId)->first();

        if (!$badge->isPurchasable) {
            return response()->json(["errors" => [
                "message" => "Not Purchasable."
            ]], Response::HTTP_BAD_REQUEST);
        }

        //check if user can afford
        if ($user->coins < $badge->cost) {
            return response()->json(["errors" => [
                "message" => "Insufficient funds."
            ]], Response::HTTP_BAD_REQUEST);
        }

        //check if user already has the badge
        $alreadyHasBadge = $user->badges->filter(function (Badge $badge) use ($badgeId) {
            if ($badge->id == $badgeId) {
                return $badge;
            }

            return null;
        });

        if ($alreadyHasBadge->count()) {
            return response()->json(["errors" => [
                "message" => "You already have this badge."
            ]], Response::HTTP_BAD_REQUEST);
        }

        $coinTransaction = new CoinTransaction();
        $coinTransaction->userId      = $user->id;
        $coinTransaction->amount      = -1 * $badge->cost;
        $coinTransaction->description = "Purchased badge: " . $badge->name;
        $coinTransaction->save();

        $user->coins -= $badge->cost;
        $user->save();

        //add badge to user
        $user->badges()->attach($badgeId);

        return response()->json(["message" => "Successfully purchased the badge."]);
    }
}
