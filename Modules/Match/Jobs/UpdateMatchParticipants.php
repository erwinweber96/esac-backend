<?php


namespace Modules\Match\Jobs;


use Modules\Match\Entities\MatchModel;
use Modules\Match\Http\Requests\UpdateMatchParticipantsRequest;
use Modules\Match\Repositories\MatchRepository;

/**
 * Class UpdateMatchParticipants
 * @package Modules\Match\Jobs
 */
class UpdateMatchParticipants
{
    /** @var MatchRepository $matchRepository */
    private $matchRepository;

    /**
     * UpdateMatchParticipants constructor.
     * @param MatchRepository $matchRepository
     */
    public function __construct(MatchRepository $matchRepository)
    {
        $this->matchRepository = $matchRepository;
    }

    /**
     * @param UpdateMatchParticipantsRequest $request
     * @return MatchModel
     */
    public function execute(UpdateMatchParticipantsRequest $request)
    {
        $participantIds = $request->input("participants");
        $matchId        = $request->input("matchId");

        /** @var MatchModel $match */
        $match = $this->matchRepository->show($matchId);

        return $this->matchRepository->syncParticipants($match, $participantIds);
    }
}
