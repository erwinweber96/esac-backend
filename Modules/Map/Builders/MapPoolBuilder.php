<?php


namespace Modules\Map\Builders;


use App\Model\Builder;

/**
 * Class MapPoolBuilder
 * @package Modules\Map\Builders
 */
class MapPoolBuilder implements Builder
{
    /** @var array $mapPool */
    private $mapPool;

    public function prepare(): Builder
    {
        $this->mapPool = [];
        return $this;
    }

    public function build()
    {
        return $this->mapPool;
    }

    public function setName(string $name): self
    {
        $this->mapPool['name'] = $name;
        return $this;
    }

    public  function setMxId(int $mxId): self
    {
        $this->mapPool['mx_id'] = $mxId;
        return $this;
    }

    public function setEventId(int $eventId): self
    {
        $this->mapPool['event_id'] = $eventId;
        return $this;
    }
}
