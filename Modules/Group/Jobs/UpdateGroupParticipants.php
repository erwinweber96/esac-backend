<?php


namespace Modules\Group\Jobs;


use Modules\Group\Entities\Group;
use Modules\Group\Http\Requests\UpdateGroupParticipantsRequest;
use Modules\Group\Repositories\GroupRepository;

class UpdateGroupParticipants
{
    /** @var GroupRepository $groupRepository */
    private $groupRepository;

    /**
     * UpdateGroupParticipants constructor.
     * @param GroupRepository $groupRepository
     */
    public function __construct(GroupRepository $groupRepository)
    {
        $this->groupRepository = $groupRepository;
    }

    /**
     * @param UpdateGroupParticipantsRequest $request
     * @return Group
     */
    public function execute(UpdateGroupParticipantsRequest $request)
    {
        $participantIds = $request->input("participants");
        $groupId        = $request->input("groupId");

        /** @var Group $group */
        $group = $this->groupRepository->show($groupId);

        return $this->groupRepository->syncParticipants($group, $participantIds);
    }
}
