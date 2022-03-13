<?php


namespace Modules\Play\Http\Controllers;


use Modules\Market\Entities\Badge;
use Modules\Play\Entities\CaseDrop;
use Modules\User\Entities\CoinTransaction;
use Modules\User\Entities\User;
use Symfony\Component\HttpFoundation\Response;

class CaseController
{
    public function markDropsAsSeen()
    {
        /** @var User $user */
        $user = auth()->user();

        CaseDrop::where("user_id", $user->id)->update([
            "seen" => true
        ]);

        return response()->json(['message' => 'Success.'], Response::HTTP_OK);
    }

    public function openCase($id)
    {
        /** @var CaseDrop $caseDrop */
        $caseDrop = CaseDrop::where("id", $id)->first();

        if (!$caseDrop) {
            return response()->json(["error" =>
                [
                    "message" => "No such case or case already opened."
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        /** @var User $user */
        $user = auth()->user();

        if ($user->coins < CaseDrop::UNBOXING_COST) {
            return response()->json(["error" =>
                [
                    "message" => "Not enough coins."
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($caseDrop->userId != $user->id) {
            return response()->json(["error" =>
                [
                    "message" => "No such case or case already opened."
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        $badge = $this->getDroppedBadge($caseDrop)[0];
        $user->badges()->attach($badge->id);
        $caseDrop->delete();
        $user->coins -= CaseDrop::UNBOXING_COST;
        $user->save();

        $coinTransaction = new CoinTransaction();

        $coinTransaction->userId = $user->id;
        $coinTransaction->amount = (-1) *  CaseDrop::UNBOXING_COST;
        $coinTransaction->description = "Case unboxing";

        $coinTransaction->save();

        return $badge;
    }

    private function getDroppedBadge(CaseDrop $caseDrop)
    {
        //TODO: get badges from case
        $badges = Badge::where("case_id", $caseDrop->caseId)->get();

        //TODO: draw one
        $rand = rand(1, 50000);

        if ($rand > CaseDrop::THRESHOLD_RARE) {
            //unique dropped
            /** @var Badge $uniqueBadge */
            $uniqueBadge = $badges->where("description", "Unique");

            $alreadyDropped = \DB::table("badge_user")->where("badge_id", $uniqueBadge->id)->count();

            if (!$alreadyDropped) {
                return $uniqueBadge;
            }
        }

        if ($rand > CaseDrop::THRESHOLD_UNCOMMON) {
            //rare dropped
            return $badges->where("description", "Rare")->random(1);
        }

        if ($rand > CaseDrop::THRESHOLD_COMMON) {
            //uncommon dropped
            return $badges->where("description", "Uncommon")->random(1);
        }

        //common dropped
        return $badges->where("description", "Common")->random(1);
    }
}
