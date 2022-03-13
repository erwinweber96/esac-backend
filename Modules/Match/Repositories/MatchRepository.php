<?php


namespace Modules\Match\Repositories;


use App\Model\Builder;
use App\Repository\Repository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Modules\GameServer\Entities\GameServer;
use Modules\GameServer\Events\GameServerAdded;
use Modules\Match\Entities\MatchModel;
use Modules\Match\Jobs\SendMatchCreatedEventToDiscordWebhook;

/**
 * Class MatchRepository
 * @package Modules\Match\Repositories
 */
class MatchRepository implements Repository
{
    /** @var MatchModel $match */
    private $match;

    /**
     * MatchRepository constructor.
     * @param MatchModel $match
     */
    public function __construct(MatchModel $match)
    {
        $this->match = $match;
    }

    public function all(): Collection
    {
        // TODO: Implement all() method.
    }

    /**
     * @param Builder $data
     * @return Model
     */
    public function create(Builder $data): Model
    {
        return $this->match->create($data->build());
    }

    public function update(Builder $data, $id): bool
    {
        /** @var MatchModel $match */
        $match = $this->match->where("id", $id)->first();
        return $match->update($data->build());
    }

    public function delete($id): bool
    {
        return $this->match->where("id", $id)->delete();
    }

    /**
     * @param $id
     * @return Model
     */
    public function show($id): Model
    {
        //all relations: 16s
        //without results: 7s
        //without map pool: 11s

        /** @var MatchModel $match */
        $match = $this->match->with([
            'group',
            'participants',
            'group.participants',
            'participants.user',
            'participants.page',
            'participants.page.user',
            'group.participants.user',
            'group.participants.page',
            'group.formats',
            'formats',
            'gameServers',
            'liveStreams',
            'liveStreams.link',
            'vods',
            'vods.link'
        ])
            ->where("id", $id)
            ->first();
        $end = time();

        return $match;
    }

    /**
     * @param MatchModel $match
     * @param $participants
     * @return MatchModel
     */
    public function syncParticipants(MatchModel $match, $participants): MatchModel
    {
        $match->participants()->sync($participants);
        $match = $match->fresh(["participants"]);
        SendMatchCreatedEventToDiscordWebhook::dispatch($match);
        return $match;
    }

    /**
     * @param MatchModel $match
     * @param $formats
     * @return MatchModel
     */
    public function syncFormats(MatchModel $match, $formats): MatchModel
    {
        $match->formats()->sync($formats);
        return $match;
    }

    /**
     * @param MatchModel $match
     * @param GameServer $gameServer
     * @return MatchModel|null
     */
    public function addGameServer(MatchModel $match, GameServer $gameServer)
    {
        $match->gameServers()->attach($gameServer->id);
        $gameServer->fresh(['matches']);
        event(new GameServerAdded($gameServer));
        return $match->fresh(["gameServers"]);
    }

    /**
     * @param MatchModel $match
     * @param $gameServerId
     * @return MatchModel
     */
    public function removeGameServer(MatchModel $match, $gameServerId): MatchModel
    {
        $match->gameServers()->detach($gameServerId);
        return $match;
    }
}
