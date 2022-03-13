<?php


namespace Modules\Match\Jobs;


use Modules\GameServer\Builders\GameServerBuilder;
use Modules\Match\Http\Requests\AddGameServerToMatchRequest;

class BuildGameServerFromAddToMatchRequest
{
    /** @var GameServerBuilder $gameServerBuilder */
    private $gameServerBuilder;

    /**
     * BuildGameServerFromAddToMatchRequest constructor.
     * @param GameServerBuilder $gameServerBuilder
     */
    public function __construct(GameServerBuilder $gameServerBuilder)
    {
        $this->gameServerBuilder = $gameServerBuilder->prepare();
    }

    /**
     * @param AddGameServerToMatchRequest $request
     * @return GameServerBuilder
     */
    public function execute(AddGameServerToMatchRequest $request)
    {
        return $this->gameServerBuilder
            ->setName($request->input("name"))
            ->setUrl($request->input('serverLink'))
            ->setPassword($request->input("serverPassword") ?? "")
            ->setPending($request->input("pending"));
    }
}
