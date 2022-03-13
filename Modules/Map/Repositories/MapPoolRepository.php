<?php


namespace Modules\Map\Repositories;


use App\Model\Builder;
use App\Repository\Repository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Modules\Map\Entities\MapPool;

class MapPoolRepository implements Repository
{
    /** @var MapPool $mapPool */
    private $mapPool;

    /**
     * MapPoolRepository constructor.
     * @param MapPool $mapPool
     */
    public function __construct(MapPool $mapPool)
    {
        $this->mapPool = $mapPool;
    }

    public function all(): Collection
    {
        // TODO: Implement all() method.
    }

    public function create(Builder $data): Model
    {
        return $this->mapPool->create($data->build());
    }

    public function update(Builder $data, $id): bool
    {
        return $this->mapPool->where("id", $id)->update($data->build());
    }

    public function delete($id): bool
    {
        return $this->mapPool->where("id", $id)->delete();
    }

    public function show($id): Model
    {
        return $this->mapPool->where("id", $id)->first();
    }

}
