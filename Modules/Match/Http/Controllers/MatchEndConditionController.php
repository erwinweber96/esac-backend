<?php


namespace Modules\Match\Http\Controllers;


use Illuminate\Support\Str;
use Modules\Match\Entities\MatchEndCondition;
use Modules\Match\Http\Requests\CreateMatchEndConditionRequest;

class MatchEndConditionController
{
    public function create(CreateMatchEndConditionRequest $request)
    {
        $request->validated();

        $matchEndConditionData = [];
        foreach ($request->toArray() as $key => $value) {
            $matchEndConditionData[Str::snake($key)] = $value;
        }

        if ($request->id) {
            return MatchEndCondition::where("id", $request->id)->update($matchEndConditionData);
        }

        return MatchEndCondition::create($matchEndConditionData);
    }
}
