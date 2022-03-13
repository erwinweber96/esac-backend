<?php

namespace Modules\Match\Jobs;

use Modules\Match\Builders\MatchResultBuilder;
use Modules\Match\Http\Requests\MatchMapResultRequest;
use Modules\Match\Http\Requests\MatchResultRequest;

class BuildMatchMapResult
{
    /** @var MatchResultBuilder $matchResultBuilder */
    private $matchResultBuilder;

    /**
     * BuildMatchMapResult constructor.
     * @param MatchResultBuilder $matchResultBuilder
     */
    public function __construct(MatchResultBuilder $matchResultBuilder)
    {
        $this->matchResultBuilder = $matchResultBuilder->prepare();
    }

    public function execute(MatchMapResultRequest $request)
    {
        return $this->matchResultBuilder
            ->setResult($request->input("result"))
            ->setParticipantId($request->input("participantId"))
            ->setIsTotalResult($request->input("isTotalResult"))
            ->setMatchId($request->input("matchId"))
            ->setPending($request->input("pending"))
            ->setMapId($request->input("mapId"));
    }
}
