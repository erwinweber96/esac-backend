<?php


namespace Modules\Link\Repositories;


use App\Model\Builder;
use App\Repository\Repository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Modules\Link\Entities\Link;

/**
 * Class LinkRepository
 * @package Modules\Link\Repositories
 */
class LinkRepository implements Repository
{
    /** @var Link $link */
    private $link;

    /**
     * LinkRepository constructor.
     * @param Link $link
     */
    public function __construct(Link $link)
    {
        $this->link = $link;
    }

    public function all(): Collection
    {
        // TODO: Implement all() method.
    }

    public function create(Builder $data): Model
    {
        return $this->link->create($data->build());
    }

    public function update(Builder $data, $id): bool
    {
        // TODO: Implement update() method.
    }

    public function delete($id): bool
    {
        // TODO: Implement delete() method.
    }

    public function show($id): Model
    {
        // TODO: Implement show() method.
    }
}
