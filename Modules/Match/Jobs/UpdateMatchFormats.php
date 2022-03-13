<?php


namespace Modules\Match\Jobs;


use Modules\Match\Entities\MatchModel;
use Modules\Match\Http\Requests\UpdateMatchFormatsRequest;
use Modules\Match\Repositories\MatchRepository;

class UpdateMatchFormats
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
     * @param UpdateMatchFormatsRequest $request
     * @return MatchModel
     */
    public function execute(UpdateMatchFormatsRequest $request)
    {
        $formatIds = $request->input("formats");
        $matchId        = $request->input("matchId");

        /** @var MatchModel $match */
        $match = $this->matchRepository->show($matchId);

        return $this->matchRepository->syncFormats($match, $formatIds);
    }
}
