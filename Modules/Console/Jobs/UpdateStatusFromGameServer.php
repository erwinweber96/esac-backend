<?php

namespace Modules\Console\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Response;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Console\Entities\DedicatedController;
use Modules\Console\Events\MatchEnded;
use Modules\Console\Services\DedicatedControllerService;
use Modules\GameServer\Builders\GameServerBuilder;
use Modules\GameServer\Entities\GameServer;
use Modules\GameServer\Repositories\GameServerRepository;
use Modules\Match\Entities\MatchModel;
use Modules\Match\Jobs\SendMatchStatusUpdatedEventToDiscordWebhook;
use Modules\Match\Repositories\MatchRepository;

class UpdateStatusFromGameServer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $matchId;
    public $matchStatusId;
    public $controllerStatusId;
    public $isCancelled;
    public $joinLink;

    /**
     * @param $matchId
     * @param $matchStatusId
     * @param $controllerStatusId
     * @param $isCancelled
     * @param $joinLink
     */
    public function __construct($matchId, $matchStatusId, $controllerStatusId, $isCancelled, $joinLink)
    {
        $this->matchId = $matchId;
        $this->matchStatusId = $matchStatusId;
        $this->controllerStatusId = $controllerStatusId;
        $this->isCancelled = $isCancelled;
        $this->joinLink = $joinLink;
    }


    public function handle()
    {
        /** @var MatchModel $match */
        $match = MatchModel::where("id", $this->matchId)->first();

        /** @var GameServerBuilder $gameServerBuilder */
        $gameServerBuilder = app(GameServerBuilder::class);

        /** @var GameServerRepository $gameServerRepository */
        $gameServerRepository = app(GameServerRepository::class);

        /** @var MatchRepository $matchRepository */
        $matchRepository = app(MatchRepository::class);

        /** @var DedicatedControllerService $dedicatedControllerService */
        $dedicatedControllerService = app(DedicatedControllerService::class);

        if ($this->matchStatusId == MatchModel::STATUS_LIVE) {
            /** @var GameServerBuilder $builder */
            $builder = $gameServerBuilder->prepare();

            $builder->setName("Console Dedicated Controller")
                ->setPending(false)
                ->setUrl($this->joinLink)
                ->setPassword("");

            /** @var GameServer $gameServer */
            $gameServer = $gameServerRepository->create($builder);

            $matchRepository->addGameServer($match, $gameServer);
        }

        if ($this->matchStatusId == MatchModel::STATUS_ENDED) {
            if ($match->statusId != MatchModel::STATUS_ENDED) {
                try {
                    if (!$this->isCancelled) {
                        $dedicatedControllerService->calculateElo($match);
                        event(new MatchEnded($match));
                    }
                } catch (\Throwable $exception) {
                    dd($exception);
                }
            }
        }

        $match->statusId = $this->matchStatusId;
        $match->save();

        DedicatedController::where("match_id", $this->matchId)->update([
            "status_id" => $this->controllerStatusId
        ]);
    }
}
