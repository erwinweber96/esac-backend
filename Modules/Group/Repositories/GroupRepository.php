<?php


namespace Modules\Group\Repositories;


use App\Model\Builder;
use App\Repository\Repository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Modules\Group\Entities\Group;

class GroupRepository implements Repository
{
    /** @var Group $group */
    private $group;

    /**
     * GroupRepository constructor.
     * @param Group $group
     */
    public function __construct(Group $group)
    {
        $this->group = $group;
    }

    public function all(): Collection
    {
        // TODO: Implement all() method.
    }

    public function create(Builder $data): Model
    {
        return $this->group->create($data->build());
    }

    public function update(Builder $data, $id): bool
    {
        return $this->group->where("id", $id)->update($data->build());
    }

    public function delete($id): bool
    {
        return $this->group->where("id", $id)->delete();
    }

    public function show($id): Model
    {
        return $this->group
            ->where("id", $id)
            ->with($this->group->relations)
            ->first();
    }

    /**
     * @param Group $group
     * @param $formatId
     * @return Group
     */
    public function attachFormat(Group $group, $formatId)
    {
        $group->formats()->attach($formatId);
        return $group;
    }

    /**
     * @param Group $group
     * @param $formatId
     * @return Group
     */
    public function detachFormat(Group $group, $formatId)
    {
        $group->formats()->detach($formatId);
        return $group;
    }

    /**
     * @param Group $group
     * @param $formats
     * @return Group
     */
    public function syncFormats(Group $group, $formats)
    {
        $group->formats()->sync($formats);
        return $group;
    }

    /**
     * @param Group $group
     * @param $participants
     * @return Group
     */
    public function syncParticipants(Group $group, $participants)
    {
        $group->participants()->sync($participants);
        return $group->fresh(["participants"]);
    }
}
