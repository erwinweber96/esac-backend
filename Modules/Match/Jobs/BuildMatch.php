<?php


namespace Modules\Match\Jobs;


use Modules\Match\Builders\MatchBuilder;
use Modules\Match\Http\Requests\CreateMatchRequest;

/**
 * Class BuildMatch
 * @package Modules\Match\Jobs
 */
class BuildMatch
{
    /** @var MatchBuilder */
    private $matchBuilder;

    /**
     * BuildMatch constructor.
     * @param MatchBuilder $matchBuilder
     */
    public function __construct(MatchBuilder $matchBuilder)
    {
        $this->matchBuilder = $matchBuilder->prepare();
    }

    /**
     * @param CreateMatchRequest $request
     * @return MatchBuilder
     */
    public function execute(CreateMatchRequest $request)
    {
        //TODO: if group has inheritable formats, link formats to this match
        
        return $this->matchBuilder
            ->setName($request->input("name"))
            ->setGroupId($request->input("groupId"))
            ->setDate($request->input("date"))
            ->setTime($request->input("time"))
            ->setMapPoolId($request->input("mapPoolId"));
    }
}
