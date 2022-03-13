<?php


namespace Modules\Match\Events;


use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;
use Modules\Match\Entities\MatchResult;

class MatchResultUpdated implements ShouldBroadcastNow
{
    use SerializesModels;

    /** @var MatchResult $match */
    private $matchResult;

    /**
     * MatchResultCreated constructor.
     * @param MatchResult $matchResult
     */
    public function __construct(MatchResult $matchResult)
    {
        $this->matchResult = $matchResult;
    }

    /**
     * @return Channel|Channel[]|PrivateChannel
     */
    public function broadcastOn()
    {
        return new Channel("match_".$this->matchResult->matchId);
    }

    public function broadcastAs()
    {
        return "match_result_updated";
    }
}
