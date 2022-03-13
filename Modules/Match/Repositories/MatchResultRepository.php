<?php


namespace Modules\Match\Repositories;


use App\Model\Builder;
use App\Repository\Repository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Modules\Match\Entities\MatchResult;

class MatchResultRepository implements Repository
{
    /** @var MatchResult $matchResult */
    private $matchResult;

    /**
     * MatchResultRepository constructor.
     * @param MatchResult $matchResult
     */
    public function __construct(MatchResult $matchResult)
    {
        $this->matchResult = $matchResult;
    }

    public function all(): Collection
    {
        // TODO: Implement all() method.
    }

    public function create(Builder $data): Model
    {
        return $this->matchResult->create($data->build());
    }

    public function update(Builder $data, $id): bool
    {
        return $this->matchResult->update($data->build());
    }

    public function delete($id): bool
    {
        return $this->matchResult->where("id", $id)->delete();
    }

    public function show($id): Model
    {
        // TODO: Implement show() method.
    }

    /**
     * @param $id
     * @return bool
     */
    public function approve($id)
    {
        return $this->matchResult->where("id", $id)->update(["pending" => false]);
    }
}
