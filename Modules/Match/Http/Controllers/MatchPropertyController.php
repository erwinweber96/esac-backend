<?php


namespace Modules\Match\Http\Controllers;


use Illuminate\Http\Request;
use Modules\Match\Entities\MatchProperty;

/**
 * Class MatchPropertyController
 * @package Modules\Match\Http\Controllers
 */
class MatchPropertyController
{
    public function get($matchId)
    {
        return MatchProperty::where("match_id", $matchId)->get();
    }

    public function create(Request $request)
    {
        $matchProperty = new MatchProperty();

        $matchProperty->matchId  = $request->input("matchId");
        $matchProperty->key      = $request->input("key");
        $matchProperty->value    = $request->input("value");
        $matchProperty->readOnly = true;

        $matchProperty->save();
        return $matchProperty;
    }

    public function delete($propertyId)
    {
        return MatchProperty::where("id", $propertyId)->delete();
    }
}
