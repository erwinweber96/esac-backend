<?php


namespace Modules\GameServer\Repositories;


use App\Model\Builder;
use App\Repository\Repository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Modules\GameServer\Entities\GameServer;

/**
 * Class GameServerRepository
 * @package Modules\GameServer\Repositories
 */
class GameServerRepository implements Repository
{
    /** @var GameServer $gameServer */
    private $gameServer;

    /**
     * GameServerRepository constructor.
     * @param GameServer $gameServer
     */
    public function __construct(GameServer $gameServer)
    {
        $this->gameServer = $gameServer;
    }

    public function all(): Collection
    {
        // TODO: Implement all() method.
    }

    public function create(Builder $data): Model
    {
        return $this->gameServer->create($data->build());
    }

    public function update(Builder $data, $id): bool
    {
        // TODO: Implement update() method.
    }

    public function delete($id): bool
    {
        return $this->gameServer->where("id", $id)->delete();
    }

    public function show($id): Model
    {
        return $this->gameServer->where("id", $id)->first();
    }

    public function approveGameServer($gameServerId)
    {
        $gameServer = $this->show($gameServerId);
        return $gameServer->update(["pending" => false]);
    }
}
