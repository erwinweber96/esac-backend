<?php

namespace Modules\Console\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Event\Entities\Participant;
use Modules\Match\Entities\MatchModel;
use Modules\Match\Entities\MatchResult;

class AddResultsFromGameServer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $results;

    public $matchId;

    /**
     * @param $results
     * @param $matchId
     */
    public function __construct($results, $matchId)
    {
        $this->results = $results;
        $this->matchId = $matchId;
    }

    public function handle()
    {
        /** @var MatchModel $match */
        $match = MatchModel::where("id", $this->matchId)->first();
        $participants = $match->participants()->get();

        foreach ($this->results as $result) {
            $participants->filter(function (Participant $participant) use ($result) {
                if ($participant->event->isTeamEvent) {
                    if ($participant->page->name == $result['teamName']) {
                        $matchResult = new MatchResult();

                        $matchResult->participantId = $participant->id;
                        $matchResult->result = $result['score'];
                        $matchResult->matchId = $this->matchId;
                        $matchResult->isTotalResult = true;
                        $matchResult->pending = false;

                        $matchResult->save();
                    }
                } else {
                    if ($participant->user->tmNickname == $result['nickname']) {
                        $matchResult = new MatchResult();

                        $matchResult->participantId = $participant->id;
                        $matchResult->result = $result['score'];
                        $matchResult->matchId = $this->matchId;
                        $matchResult->isTotalResult = true;
                        $matchResult->pending = false;

                        $matchResult->save();
                    }
                }
            });
        }
    }
}
