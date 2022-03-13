<?php

namespace Modules\Match\Events;

use Illuminate\Broadcasting\Channel;
use Modules\Match\Entities\MatchModel;

class MatchStatusUpdated
{
    /** @var MatchModel $match */
    public $match;

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
        return "match_status_updated";
    }
}
