<?php


namespace Modules\Match\Events;


use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;
use Modules\Match\Entities\MatchModel;

/**
 * Class MatchUpdated
 * @package Modules\Match\Events
 */
class MatchUpdated implements ShouldBroadcastNow
{
    use SerializesModels;

    /** @var MatchModel $match */
    public $match;

    /**
     * MatchUpdated constructor.
     * @param MatchModel $match
     */
    public function __construct(MatchModel $match)
    {
        $this->match = $match;
    }

    /**
     * @return Channel|Channel[]|PrivateChannel
     */
    public function broadcastOn()
    {
        return new Channel("match_".$this->match->id);
    }

    public function broadcastAs()
    {
        return "match_updated";
    }
}
