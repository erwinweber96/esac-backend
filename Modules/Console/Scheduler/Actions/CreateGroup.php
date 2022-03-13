<?php


namespace Modules\Console\Scheduler\Actions;


use Modules\Group\Builders\GroupBuilder;
use Modules\Group\Entities\Group;
use Modules\Group\Entities\GroupProperty;
use Modules\Group\Repositories\GroupRepository;

/**
 * Class CreateGroup
 * @package Modules\Console\Scheduler\Actions
 */
class CreateGroup extends ScheduledActionHandler
{
    public int      $eventId    = 0;
    public string   $groupName  = "";
    public array    $properties = [];

    public function run()
    {
        /** @var GroupBuilder $groupBuilder */
        $groupBuilder = app(GroupBuilder::class);

        $groupBuilder->prepare();
        $groupBuilder->setEventId($this->eventId);
        $groupBuilder->setIsTypeTree(false);
        $groupBuilder->setMaxSize(1);
        $groupBuilder->setMinSize(1);
        $groupBuilder->setName($this->groupName);

        /** @var GroupRepository $groupRepository */
        $groupRepository = app(GroupRepository::class);

        /** @var Group $group */
        $group = $groupRepository->create($groupBuilder);

        foreach ($this->properties as $property) {
            $groupProperty = new GroupProperty();

            $groupProperty->key      = $property['key'];
            $groupProperty->value    = $property['value'];
            $groupProperty->readOnly = true;
            $groupProperty->groupId  = $group->id;

            $groupProperty->save();
        }
    }
}
