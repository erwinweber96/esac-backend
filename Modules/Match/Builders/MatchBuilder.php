<?php


namespace Modules\Match\Builders;


use App\Model\Builder;
use Carbon\Carbon;

/**
 * Class MatchBuilder
 * @package Modules\Match\Builders
 */
class MatchBuilder implements Builder
{
    /** @var array $match */
    private $match;

    /** @var string $date */
    private $date;

    /** @var string $time */
    private $time;

    /**
     * @return Builder
     */
    public function prepare(): Builder
    {
        $this->match = [];
        $this->date  = "";
        $this->time  = "";

        return $this;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function build()
    {
        $date = new Carbon($this->date." ".$this->time);
        $this->match['date'] = $date;
        return $this->match;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->match['name'] = $name;
        return $this;
    }

    /**
     * @param string $date
     * @return $this
     */
    public function setDate(string $date): self
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @param string $time
     * @return $this
     */
    public function setTime(string $time): self
    {
        $this->time = $time;
        return $this;
    }

    /**
     * @param int $eventId
     * @return $this
     */
    public function setEventId(int $eventId): self
    {
        $this->match['event_id'] = $eventId;
        return $this;
    }

    /**
     * @param int $groupId
     * @return $this
     */
    public function setGroupId(int $groupId): self
    {
        $this->match['group_id'] = $groupId;
        return $this;
    }

    /**
     * @param int $mapPoolId
     * @return $this
     */
    public function setMapPoolId(int $mapPoolId): self
    {
        $this->match['map_pool_id'] = $mapPoolId;
        return $this;
    }
}
