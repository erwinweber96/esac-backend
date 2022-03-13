<?php


namespace Modules\Group\Jobs;


use Modules\Group\Builders\GroupBuilder;
use Modules\Group\Http\Requests\CreateGroupRequest;

class BuildGroup
{
    /** @var GroupBuilder */
    private $groupBuilder;

    /**
     * BuildGroup constructor.
     * @param GroupBuilder $groupBuilder
     */
    public function __construct(GroupBuilder $groupBuilder)
    {
        $this->groupBuilder = $groupBuilder;
    }

    /**
     * @param CreateGroupRequest $request
     * @return GroupBuilder
     */
    public function execute(CreateGroupRequest $request)
    {
        /** @var GroupBuilder $builder */
        $builder = $this->groupBuilder->prepare();

        return $builder
            ->setName($request->input("name"))
            ->setMinSize(0)
            ->setMaxSize(0)
            ->setIsTypeTree($request->input("isTypeTree"))
            ->setEventId($request->input("eventId"))
            ->setContainerId($request->input("containerId"));
    }
}
