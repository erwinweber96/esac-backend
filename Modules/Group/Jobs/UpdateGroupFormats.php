<?php


namespace Modules\Group\Jobs;


use Modules\Group\Entities\Format;
use Modules\Group\Entities\Group;
use Modules\Group\Http\Requests\UpdateGroupFormatsRequest;
use Modules\Group\Repositories\GroupRepository;

class UpdateGroupFormats
{
    /** @var GroupRepository */
    private $groupRepository;

    /**
     * UpdateGroupFormats constructor.
     * @param GroupRepository $groupRepository
     */
    public function __construct(GroupRepository $groupRepository)
    {
        $this->groupRepository = $groupRepository;
    }

    /**
     * @param UpdateGroupFormatsRequest $request
     * @return Group
     */
    public function execute(UpdateGroupFormatsRequest $request)
    {
        $groupId = $request->input("groupId");

        /** @var Group $group */
        $group = $this->groupRepository->show($groupId);

        return $this->groupRepository
            ->syncFormats($group, $request->input("formats"));
    }
}
