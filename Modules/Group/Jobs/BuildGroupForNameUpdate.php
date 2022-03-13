<?php


namespace Modules\Group\Jobs;


use Modules\Group\Builders\GroupBuilder;
use Modules\Group\Http\Requests\UpdateGroupNameRequest;

class BuildGroupForNameUpdate
{
    /** @var GroupBuilder $groupBuilder */
    private $groupBuilder;

    /**
     * UpdateGroupName constructor.
     * @param GroupBuilder $groupBuilder
     */
    public function __construct(GroupBuilder $groupBuilder)
    {
        $this->groupBuilder = $groupBuilder->prepare();
    }

    public function execute(UpdateGroupNameRequest $request)
    {
        return $this->groupBuilder->setName($request->groupName);
    }
}
