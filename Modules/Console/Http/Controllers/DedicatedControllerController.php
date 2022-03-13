<?php


namespace Modules\Console\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Console\Entities\DedicatedController;
use Modules\Console\Events\MatchEnded;
use Modules\Console\Jobs\AddResultsFromGameServer;
use Modules\Console\Jobs\UpdateStatusFromGameServer;
use Modules\Console\Services\DedicatedControllerService;
use Modules\Console\Traits\RequiresConsoleAccess;
use Modules\Event\Entities\Participant;
use Modules\GameServer\Builders\GameServerBuilder;
use Modules\GameServer\Entities\GameServer;
use Modules\GameServer\Repositories\GameServerRepository;
use Modules\Match\Entities\MatchModel;
use Modules\Match\Entities\MatchResult;
use Modules\Match\Repositories\MatchRepository;


/**
 * Class DedicatedControllerController
 * @package Modules\Console\Http\Controllers
 *
 * Handles requests coming from the JS Dedicated Controller.
 */
class DedicatedControllerController
{
    use RequiresConsoleAccess;

    /** @var GameServerRepository $gameServerRepository */
    private $gameServerRepository;

    /** @var MatchRepository $matchRepository */
    private $matchRepository;

    /** @var GameServerBuilder $gameServerBuilder */
    private $gameServerBuilder;

    /** @var DedicatedControllerService $dedicatedControllerService */
    private $dedicatedControllerService;

    /**
     * DedicatedControllerController constructor.
     * @param GameServerRepository $gameServerRepository
     * @param MatchRepository $matchRepository
     * @param GameServerBuilder $gameServerBuilder
     * @param DedicatedControllerService $dedicatedControllerService
     */
    public function __construct(GameServerRepository $gameServerRepository, MatchRepository $matchRepository, GameServerBuilder $gameServerBuilder, DedicatedControllerService $dedicatedControllerService)
    {
        $this->gameServerRepository = $gameServerRepository;
        $this->matchRepository = $matchRepository;
        $this->gameServerBuilder = $gameServerBuilder;
        $this->dedicatedControllerService = $dedicatedControllerService;
    }

    /**
     * @param Request $request
     * @return DedicatedController
     */
    public function add(Request $request)
    {
        /** @var DedicatedController $controller */
        $controller = DedicatedController::where("port", $request->input("port"))->first();

        if ($controller) {
            $controller->delete();
        }

        return DedicatedController::create([
            "port" => $request->input("port")
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addResults(Request $request)
    {
        $results = $request->input("results");
        $matchId = $request->input("matchId");

        AddResultsFromGameServer::dispatch($results, $matchId);

        return response()->json(["message" => "success"], Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request)
    {
        $matchId = $request->input("matchId");
        $matchStatusId = $request->input("matchStatusId");
        $controllerStatusId = $request->input("controllerStatusId");
        $isCancelled = $request->input("isCancelled");
        $joinLink = $request->input("joinLink");

        UpdateStatusFromGameServer::dispatch(
            $matchId,
            $matchStatusId,
            $controllerStatusId,
            $isCancelled,
            $joinLink
        );

        return response()->json(["message" => "success"], Response::HTTP_OK);
    }

    public function endMatch(Request $request)
    {
        $matchId = $request->input("matchId");

        /** @var MatchModel $match */
        $match = MatchModel::where("id", $matchId)->first();

        $this->dedicatedControllerService->calculateElo($match);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function cancelMatch(Request $request)
    {
        $this->verifyConsoleAccess();

        $matchId = $request->input("matchId");

        try {
            $this->dedicatedControllerService->cancelMatch($matchId);
        } catch (\Throwable $exception) {
            return response()->json([
                "message" => "Something went wrong. Could not cancel match."
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json(["message" => "success"], Response::HTTP_OK);
    }
}
