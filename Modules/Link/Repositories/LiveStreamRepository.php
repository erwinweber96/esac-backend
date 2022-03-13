<?php


namespace Modules\Link\Repositories;


use App\Model\Builder;
use App\Repository\Repository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Modules\Match\Entities\LiveStream;

/**
 * Class LiveStreamRepository
 * @package Modules\Link\Repositories
 */
class LiveStreamRepository implements Repository
{
    /** @var LiveStream $liveStream */
    private $liveStream;

    /**
     * LiveStreamRepository constructor.
     * @param LiveStream $liveStream
     */
    public function __construct(LiveStream $liveStream)
    {
        $this->liveStream = $liveStream;
    }

    public function all(): Collection
    {
        // TODO: Implement all() method.
    }

    public function create(Builder $data): Model
    {
        return $this->liveStream->create($data->build());
    }

    public function update(Builder $data, $id): bool
    {
        // TODO: Implement update() method.
    }

    public function delete($id): bool
    {
        /** @var LiveStream $liveStream */
        $liveStream = $this->show($id);
        return $liveStream->delete();
    }

    public function show($id): Model
    {
        return $this->liveStream->where("id", $id)->first();
    }

    public function approveLiveStream($id)
    {
        /** @var LiveStream $liveStream */
        $liveStream = $this->show($id);

        return $liveStream->link->update(["pending" => false]);
    }
}
