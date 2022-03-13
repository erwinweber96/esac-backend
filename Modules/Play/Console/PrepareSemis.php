<?php

namespace Modules\Play\Console;

use Illuminate\Console\Command;
use Modules\Console\Services\DedicatedControllerService;
use Modules\Event\Entities\Event;
use Modules\Event\Entities\EventProperty;
use Modules\Match\Entities\MatchModel;
use Modules\Match\Entities\MatchResult;
use Modules\Play\Jobs\CachePlayEventsJob;
use Modules\Play\Services\HourlyEventService;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use function Sentry\captureMessage;

class PrepareSemis extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'semis:prepare';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @param DedicatedControllerService $dedicatedControllerService
     * @return mixed
     */
    public function handle(DedicatedControllerService $dedicatedControllerService)
    {
        //luam events cu play care sunt live
        $eventProperties = EventProperty::where("key", EventProperty::PLAY_ESAC_GG_EVENT)->get();
        $eventIds = $eventProperties->map(function (EventProperty $eventProperty) {
            return $eventProperty->eventId;
        });

        $liveEvents = Event::where("status_id", Event::STATUS_LIVE)
            ->where("page_id", "!=", HourlyEventService::PAGE_ID)
            ->whereIn("id", $eventIds)
            ->get();

        if (!$liveEvents) {
            return;
        }

        //verificam daca semis sunt open si quarters sunt ended
        /** @var Event $liveEvent */
        foreach ($liveEvents as $liveEvent) {
            $quarters = $liveEvent->groups[0];
            $semis    = $liveEvent->groups[1];

            $endedQuarters = $quarters->matches
                ->where('status_id', MatchModel::STATUS_ENDED)
                ->count();

            if ($endedQuarters != 4) {
                continue;
            }

            $openSemis = $semis->matches
                ->where('status_id', MatchModel::STATUS_UPCOMING)
                ->count();

            if ($openSemis != 2) {
                continue;
            }

            $winners1 = $this->getMatchWinners($quarters->matches[0]);
            $winners2 = $this->getMatchWinners($quarters->matches[1]);
            $winners3 = $this->getMatchWinners($quarters->matches[2]);
            $winners4 = $this->getMatchWinners($quarters->matches[3]);

            $allSemiPlayers = [
                $winners1[0], $winners2[0], $winners3[1], $winners4[1],
                $winners1[1], $winners2[1], $winners3[0], $winners4[0]
            ];

            $semi1Players = [
                $winners1[0], $winners2[0], $winners3[1], $winners4[1]
            ];

            $semi2Players = [
                $winners1[1], $winners2[1], $winners3[0], $winners4[0]
            ];

            $semis->participants()->sync($allSemiPlayers);
            $semis->matches[0]->participants()->sync($semi1Players);
            $semis->matches[1]->participants()->sync($semi2Players);

            try {
                $semis->save();
            } catch (\Throwable $exception) {
                //?
            }

            try {
                $dedicatedControllerService->startMatch($semis->matches[0]->id);
            } catch (\Throwable $exception) {
                \Sentry\captureException($exception);
            }

            try {
                $dedicatedControllerService->startMatch($semis->matches[1]->id);
            } catch (\Throwable $exception) {
                \Sentry\captureException($exception);
            }
        }

        CachePlayEventsJob::dispatch();
    }

    private function getMatchWinners(MatchModel $match)
    {
        $results = collect($match->totalMatchResults);
        $results = $results->sortByDesc('result', SORT_NATURAL);
        $winners = $results->take(2);
        $winners = $winners->map(function (?MatchResult $result, $participantId) {
            return $participantId;
        });
        $arrayWithObject = array_values((array)$winners);
        return array_values((array)$arrayWithObject[0]);
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [];
    }
}
