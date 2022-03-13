<?php


namespace Modules\Group\Builders;


use App\Model\Builder;

/**
 * Class GroupBuilder
 * @package Modules\Group\Builders
 */
class GroupBuilder implements Builder
{
    /** @var array $group */
    private $group;

    public function prepare(): Builder
    {
        $this->group = [];
        $this->group['type'] = "Generic";
        return $this;
    }

    public function setName(string $name): self
    {
        $this->group['name'] = $name;
        return $this;
    }

    public function setMinSize(int $size): self
    {
        $this->group['min_size'] = $size;
        return $this;
    }

    public function setMaxSize(int $size): self
    {
        $this->group['max_size'] = $size;
        return $this;
    }

    public function setIsTypeTree(bool $isTypeTree): self
    {
        $this->group['is_type_tree'] = $isTypeTree;
        return $this;
    }

    public function setEventId(int $eventId): self
    {
        $this->group['event_id'] = $eventId;
        return $this;
    }

    public function setContainerId(?int $containerId): self
    {
        if (!$containerId) {
            return $this;
        }

        $this->group['group_container_id'] = $containerId;
        return $this;
    }

    public function build()
    {
        return $this->group;
    }
}
