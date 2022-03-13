<?php


namespace Modules\Console\Events;


use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Modules\Match\Entities\MatchModel;

/**
 * Class MatchEnded
 * @package Modules\Console\Events
 */
class MatchEnded
{
    /** @var MatchModel $match */
    public $match;

    /**
     * MatchEnded constructor.
     * @param MatchModel $match
     */
    public function __construct(MatchModel $match)
    {
        $this->match = $match;
    }

    public function broadcastOn()
    {
        return new Channel("match_".$this->match->id);
    }

    public function broadcastAs()
    {
        return "match_ended";
    }
}
