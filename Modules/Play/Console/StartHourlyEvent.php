<?php


namespace Modules\Play\Console;


use Carbon\Carbon;
use Illuminate\Console\Command;
use Modules\Event\Entities\Event;
use Modules\Event\Entities\EventDate;
use Modules\Event\Entities\EventProperty;
use Modules\Play\Exceptions\NotEnoughParticipants;
use Modules\Play\Exceptions\UnexpectedPlayerCount;
use Modules\Play\Factories\FormatsFactory;
use Modules\Play\Factories\GroupsFactory;
use Modules\Play\Jobs\CachePlayEventsJob;
use Modules\Play\Services\HourlyEventService;
use function Sentry\captureException;

/**
 * Class StartHourlyEvent
 * @package Modules\Play\Console
 */
class StartHourlyEvent extends Command
{
    const COMMAND = "hourly_events:start";

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = self::COMMAND;

    private FormatsFactory $formatsFactory;
    private GroupsFactory $groupsFactory;

    /**
     * StartHourlyEvent constructor.
     * @param FormatsFactory $formatsFactory
     * @param GroupsFactory $groupsFactory
     */
    public function __construct(FormatsFactory $formatsFactory, GroupsFactory $groupsFactory)
    {
        $this->formatsFactory = $formatsFactory;
        $this->groupsFactory = $groupsFactory;
        parent::__construct();
    }

    public function handle()
    {
        $openEvents = $this->getOpenEvents();

        foreach ($openEvents as $event) {
            try {
                $event->statusId = Event::STATUS_LIVE;
                $event->save();
            } catch (\Throwable $exception) {
                $event->statusId = Event::STATUS_ENDED;
                $event->save();
                captureException($exception);
                continue;
            }

            try {
                $formats = $this->formatsFactory->make($event);
            } catch (NotEnoughParticipants | UnexpectedPlayerCount $e) {
                $event->statusId = Event::STATUS_ENDED;
                $event->save();
                captureException($e);
                continue;
            }

            try {
                $this->groupsFactory->make($event, $formats);
            } catch (NotEnoughParticipants | UnexpectedPlayerCount $e) {
                $event->statusId = Event::STATUS_ENDED;
                $event->save();
                captureException($e);
                continue;
            }
        }

        CachePlayEventsJob::dispatch();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Event[]
     */
    private function getOpenEvents()
    {
        $openHourlies = Event::where("status_id", Event::STATUS_OPEN)
            ->where("page_id", HourlyEventService::PAGE_ID)
            ->get();

        $customShowdownProps = EventProperty::where("key", EventProperty::CUSTOM_SHOWDOWN)->get();
        $customShowdownIds = $customShowdownProps->map(function (EventProperty $eventProperty) {
            return $eventProperty->eventId;
        });
        $customShowdowns = Event::whereIn("id", $customShowdownIds->toArray())
            ->where("status_id", Event::STATUS_OPEN)
            ->get();

        $openEvents = $openHourlies->merge($customShowdowns);

        return $openEvents->filter(function (Event $event) {
            $startDate = $event->dates->filter(function (EventDate $date) {
                return $date->name == EventDate::EVENT_START;
            });

            /** @var EventDate $startDate */
            $startDate = $startDate->first();

            if (!$startDate) {
                return false;
            }

            $startDate = new Carbon($startDate->date);
            $now       = Carbon::now();

            return $startDate->lte($now);
        });
    }
}
