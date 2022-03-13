<?php


namespace Modules\Group\Repositories;


use App\Model\Builder;
use App\Repository\Repository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Modules\Group\Entities\Format;

class FormatRepository implements Repository
{
    /** @var Format $format */
    private $format;

    /**
     * FormatRepository constructor.
     * @param Format $format
     */
    public function __construct(Format $format)
    {
        $this->format = $format;
    }

    public function all(): Collection
    {
        // TODO: Implement all() method.
    }

    public function create(Builder $data): Model
    {
        return $this->format->create($data->build());
    }

    public function update(Builder $data, $id): bool
    {
        return $this->format->where("id", $id)->update($data->build());
    }

    public function delete($id): bool
    {
        return $this->format->where("id", $id)->delete();
    }

    public function show($id): Model
    {
        // TODO: Implement show() method.
    }
}
