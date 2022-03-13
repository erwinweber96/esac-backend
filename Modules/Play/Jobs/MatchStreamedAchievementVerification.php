<?php


namespace Modules\Play\Jobs;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Event\Entities\Participant;
use Modules\Match\Entities\MatchModel;
use Modules\Play\Entities\PlayerMatchStream;
use Modules\Twitch\Services\TwitchService;
use function Sentry\captureException;

class MatchStreamedAchievementVerification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(MatchModel $match)
    {
        /** @var TwitchService $twitchService */
        $twitchService = app(TwitchService::class);

        foreach($match->participants as $participant) {
            if (!$participant->user->twitchAccessToken) {
                continue;
            }

            try {
                $streamInfo = $twitchService->getStreamInfo($participant->userId);
            } catch (\Throwable $exception) {
                captureException($exception);
                continue;
            }

            if (!$streamInfo) {
                continue;
            }

            if (!$streamInfo['data']) {
                continue;
            }

            if (!count($streamInfo['data'])) {
                continue;
            }

            if ($streamInfo['data'][0]['type'] == 'live') {
                $playerMatchStream = new PlayerMatchStream();

                $playerMatchStream->userId = $participant->userId;
                $playerMatchStream->matchId = $match->id;
                $playerMatchStream->hasWon = $this->hasParticipantWonMatch($participant, $match);

                $playerMatchStream->save();
            }
        }
    }

    private function hasParticipantWonMatch(Participant $participant, MatchModel $match)
    {
        $participantResult = $match->totalMatchResults[$participant->id]->result;
        $resultsOnly = [];
        foreach ($match->totalMatchResults as $totalMatchResult) {
            if ($totalMatchResult == null) {
                continue;
            }

            $resultsOnly[] = $totalMatchResult->result;
        }

        if (count($resultsOnly)) {
            rsort($resultsOnly);

            if ($match->participants->count() == 3 || $match->participants->count() == 4) {
                //is in top 2
                if ($resultsOnly[0] == $participantResult || $resultsOnly[1] == $participantResult) {
                    return true;
                }
            }

            if ($match->participants->count() == 2) {
                if ($resultsOnly[0] == $participantResult) {
                    return true;
                }
            }
        }

        return false;
    }
}
