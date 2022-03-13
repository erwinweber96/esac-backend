<?php


namespace Modules\Match\Repositories;


use App\Model\Builder;
use App\Repository\Repository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Modules\Match\Entities\Vod;

class VodRepository implements Repository
{
    /** @var Vod $vod */
    private $vod;

    /**
     * VodRepository constructor.
     * @param Vod $vod
     */
    public function __construct(Vod $vod)
    {
        $this->vod = $vod;
    }

    public function all(): Collection
    {
        // TODO: Implement all() method.
    }

    public function create(Builder $data): Model
    {
        return $this->vod->create($data->build());
    }

    public function update(Builder $data, $id): bool
    {
        // TODO: Implement update() method.
    }

    public function delete($id): bool
    {
        return $this->vod->where("id", $id)->delete();
    }

    public function show($id): Model
    {
        return $this->vod->where("id", $id)->first();
    }

    public function approveVod($id)
    {
        /** @var Vod $vod */
        $vod = $this->show($id);

        return $vod->link->update(["pending" => false]);
    }
}
