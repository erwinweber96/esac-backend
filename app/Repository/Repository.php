<?php


namespace App\Repository;


use App\Model\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Interface RepositoryInterface
 * @package App\Repository
 */
interface Repository
{
    /**
     * @return Collection
     */
    public function all(): Collection;

    /**
     * @param Builder $data
     * @return Model
     */
    public function create(Builder $data): Model;

    /**
     * @param Builder $data
     * @param $id
     * @return bool
     */
    public function update(Builder $data, $id): bool;

    /**
     * @param $id
     * @return bool
     * @throws \Exception
     */
    public function delete($id): bool;

    /**
     * @param $id
     * @return Model
     */
    public function show($id): Model;
}
