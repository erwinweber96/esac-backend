<?php

namespace Modules\Play\Console;

use Illuminate\Console\Command;
use Modules\Console\Services\DedicatedControllerService;
use Modules\Event\Entities\Event;
use Modules\Event\Entities\EventProperty;
use Modules\Group\Entities\Format;
use Modules\Group\Entities\FormatType;
use Modules\Match\Entities\MatchModel;
use Modules\Match\Entities\MatchResult;
use Modules\Play\Jobs\CachePlayEventsJob;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class PrepareFinal extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'final:prepare';

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
        $eventProperties = EventProperty::where("key", EventProperty::PLAY_ESAC_GG_EVENT)->get();
        $eventIds = $eventProperties->map(function (EventProperty $eventProperty) {
            return $eventProperty->eventId;
        });

        $liveEvents = Event::where("status_id", Event::STATUS_LIVE)
            ->whereIn("id", $eventIds)
            ->get();

        if (!$liveEvents) {
            return;
        }

        /** @var Event $liveEvent */
        foreach ($liveEvents as $liveEvent) {
            $firstGroup = $liveEvent->groups[0];
            $hasQuali = $firstGroup->formats->filter(function (Format $format) {
                if ($format->type == FormatType::TIME_ATTACK_VALUE) {
                    return true;
                }

                return false;
            });

            if ($hasQuali) {

            }

            $semis = $liveEvent->groups[1];
            $final = $liveEvent->groups[2];

            $endedSemis = $semis->matches
                ->where('status_id', MatchModel::STATUS_ENDED)
                ->count();

            if ($endedSemis != 2) {
                continue;
            }

            $openFinal = $final->matches
                ->where('status_id', MatchModel::STATUS_UPCOMING)
                ->count();

            if ($openFinal != 1) {
                continue;
            }

            $winners1 = $this->getMatchWinners($semis->matches[0]);
            $winners2 = $this->getMatchWinners($semis->matches[1]);

            $final->participants()->sync([
                $winners1[0], $winners1[1], $winners2[0], $winners2[1]
            ]);
            $final->matches[0]->participants()->sync([
                $winners1[0], $winners1[1], $winners2[0], $winners2[1]
            ]);

            try {
                $final->save();
            } catch (\Throwable $exception) {
                //?
            }

            try {
                $dedicatedControllerService->startMatch($final->matches[0]->id);
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
//            if ($result == null) {
//                return $participantId;
//            }
//            return $result->participantId;
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
