<?php


namespace Modules\User\Repositories;


use App\Model\Builder;
use App\Repository\Repository;
use Illuminate\Database\Eloquent\Collection;
use Modules\User\Entities\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UserRepository
 * @package Modules\User\Repositories
 */
class UserRepository implements Repository
{
    /** @var User $user*/
    private $user;

    /**
     * UserRepository constructor.
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function all(): Collection
    {
        return $this->user->all();
    }

    public function create(Builder $data): Model
    {
        return $this->user->create($data->build());
    }

    public function update(Builder $data, $id): bool
    {
        $user = $this->show($id);
        return $user->update($data->build());
    }

    public function delete($id): bool
    {
        $user = $this->show($id);
        return $user->delete();
    }

    public function show($id): Model
    {
        return $this->user->where("id", $id)->first();
    }
}
