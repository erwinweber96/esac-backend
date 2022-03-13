<?php

namespace Modules\Play\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Modules\Console\Exceptions\NoServerAvailable;
use Modules\Console\Services\DedicatedControllerService;
use Modules\Event\Entities\Event;
use Modules\Event\Entities\EventProperty;
use Modules\Event\Entities\Participant;
use Modules\Group\Entities\Group;
use Modules\Match\Entities\MatchModel;
use Modules\Play\Jobs\CachePlayEventsJob;

class StartEvents extends Command
{
    const COMMAND = "events:start";

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = self::COMMAND;

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
     * TODO: refactor to allow more than single-elimination tournaments
     *
     * @param DedicatedControllerService $dedicatedControllerService
     *
     * @return mixed
     */
    public function handle(DedicatedControllerService $dedicatedControllerService)
    {
        //get open matches
        $eventProperties = EventProperty::where("key", EventProperty::PLAY_ESAC_GG_EVENT)->get();
        $eventIds = $eventProperties->map(function (EventProperty $eventProperty) {
            return $eventProperty->eventId;
        });

        $openEvents = Event::where("status_id", Event::STATUS_OPEN)
            ->whereIn("id", $eventIds)
            ->get();

        if (!$openEvents) {
            return;
        }

        //check date (using lowest match time date?)
        $liveEvents = $openEvents->filter(function (Event $event) {
            /** @var Group $group */
            $group = $event->groups->first();

            /** @var MatchModel $match */
            $match = $group->matches->first();

            $date = new Carbon($match->date);

            if ($date->lte(Carbon::now())) {
                return true;
            }

            return false;
        });

        //update to live
        //close registrations
        /** @var Event $liveEvent */
        foreach ($liveEvents as $liveEvent) {
            $liveEvent->registrationOpen = false;
            $liveEvent->statusId = Event::STATUS_CONFIGURING;
            try {
                $liveEvent->save();
            } catch (\Throwable $exception) {
                //TODO: broadcast exception
            }

            /** @var Group $quarterFinals */
            $quarterFinals = $liveEvent->groups->first();

            $participantIds = $liveEvent->participants->map(function (Participant $participant) {
               return $participant->id;
            });

            $match1 = $quarterFinals->matches[0];
            $match2 = $quarterFinals->matches[1];
            $match3 = $quarterFinals->matches[2];
            $match4 = $quarterFinals->matches[3];

            if (count($participantIds) < 12) {
                $liveEvent->statusId = Event::STATUS_ENDED;

                try {
                    $liveEvent->save();
                } catch (\Throwable $exception) {
                    //TODO: broadcast exception
                }
                //TODO: send notification that match has been cancelled
                continue;
            }

            $participantIds = $participantIds->toArray();
            shuffle($participantIds);
            $quarterFinals->participants()->sync($participantIds);
            try {
                $quarterFinals->save(); //maybe not needed?
            } catch (\Throwable $exception) {
                //TODO: ?
            }

            $match1Participants = [
                $participantIds[0],
                $participantIds[4],
                $participantIds[8]
            ];
            if (isset($participantIds[12])) {
                $match1Participants[] = $participantIds[12];
            }

            $match2Participants = [
                $participantIds[1],
                $participantIds[5],
                $participantIds[9]
            ];
            if (isset($participantIds[13])) {
                $match2Participants[] = $participantIds[13];
            }

            $match3Participants = [
                $participantIds[2],
                $participantIds[6],
                $participantIds[10]
            ];
            if (isset($participantIds[14])) {
                $match3Participants[] = $participantIds[14];
            }

            $match4Participants = [
                $participantIds[3],
                $participantIds[7],
                $participantIds[11]
            ];
            if (isset($participantIds[15])) {
                $match4Participants[] = $participantIds[15];
            }

            $match1->participants()->sync($match1Participants);
            $match2->participants()->sync($match2Participants);
            $match3->participants()->sync($match3Participants);
            $match4->participants()->sync($match4Participants);

            try {
                $match1->save(); //maybe not needed?
            } catch (\Throwable $exception) {
                //TODO: ?
            }

            try {
                $match2->save(); //maybe not needed?
            } catch (\Throwable $exception) {
                //TODO: ?
            }

            try {
                $match3->save(); //maybe not needed?
            } catch (\Throwable $exception) {
                //TODO: ?
            }

            try {
                $match4->save(); //maybe not needed?
            } catch (\Throwable $exception) {
                //TODO: ?
            }

            try {
                $dedicatedControllerService->startMatch($match1->id);
            } catch (NoServerAvailable $e) {
                \Sentry\captureException($e);
            } catch (\Throwable $e) {
                \Sentry\captureException($e);
            }

            sleep(1);

            try {
                $dedicatedControllerService->startMatch($match2->id);
            } catch (NoServerAvailable $e) {
                \Sentry\captureException($e);
            } catch (\Throwable $e) {
                \Sentry\captureException($e);
            }

            sleep(1);

            try {
                $dedicatedControllerService->startMatch($match3->id);
            } catch (NoServerAvailable $e) {
                \Sentry\captureException($e);
            } catch (\Throwable $e) {
                \Sentry\captureException($e);
            }

            sleep(1);

            try {
                $dedicatedControllerService->startMatch($match4->id);
            } catch (NoServerAvailable $e) {
                \Sentry\captureException($e);
            } catch (\Throwable $e) {
                \Sentry\captureException($e);
            }

            $liveEvent->statusId = Event::STATUS_LIVE;
            try {
                $liveEvent->save();
            } catch (\Throwable $exception) {
                //TODO: broadcast exception
            }
        }

        CachePlayEventsJob::dispatch();
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
