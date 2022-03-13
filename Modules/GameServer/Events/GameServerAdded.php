<?php


namespace Modules\GameServer\Events;


use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;
use Modules\GameServer\Entities\GameServer;
use Modules\Match\Entities\MatchModel;

/**
 * Class GameServerCreated
 * @package Modules\GameServer\Events
 */
class GameServerAdded implements ShouldBroadcastNow
{
    use SerializesModels;

    /** @var GameServer $gameServer */
    public $gameServer;

    /**
     * GameServerCreated constructor.
     * @param GameServer $gameServer
     */
    public function __construct(GameServer $gameServer)
    {
        $this->gameServer = $gameServer;
    }

    /**
     * @return Channel|Channel[]|PrivateChannel
     */
    public function broadcastOn()
    {
        /** @var MatchModel $match */
        $match = $this->gameServer->matches->first();
        return new Channel("match_".$match->id);
    }

    public function broadcastAs()
    {
        return "game_server_created";
    }
}
