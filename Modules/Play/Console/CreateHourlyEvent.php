<?php


namespace Modules\Play\Console;


use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Modules\Event\Builders\EventBuilder;
use Modules\Event\Entities\Event;
use Modules\Event\Entities\EventDate;
use Modules\Event\Entities\EventProperty;
use Modules\Event\Repositories\EventRepository;
use Modules\Game\Entities\Game;
use Modules\Map\Entities\MapPool;
use Modules\Map\ManiaExchange\Entities\Track;
use Modules\Page\Entities\PageProperty;
use Modules\Play\Jobs\CachePlayEventsJob;
use Modules\Play\Services\HourlyEventService;

/**
 * Class CreateHourlyEvent
 * @package Modules\Play\Console
 */
class CreateHourlyEvent extends Command
{
    const COMMAND = "hourly_events:create";

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = self::COMMAND;

    /** @var HourlyEventService $service */
    private HourlyEventService $service;

    /** @var EventRepository $eventRepository */
    private EventRepository $eventRepository;

    /** @var EventBuilder $eventBuilder */
    private EventBuilder $eventBuilder;

    /**
     * CreateHourlyEvent constructor.
     * @param HourlyEventService $service
     * @param EventRepository $eventRepository
     * @param EventBuilder $eventBuilder
     */
    public function __construct(HourlyEventService $service, EventRepository $eventRepository, EventBuilder $eventBuilder)
    {
        parent::__construct();
        $this->service = $service;
        $this->eventRepository = $eventRepository;
        $this->eventBuilder = $eventBuilder;
    }

    public function handle()
    {
        $mapPoolProperty = $this->service->getRandomMapPool();
        $map             = $this->service->getRandomMap($mapPoolProperty);

        $event = $this->createEvent();
        $date  = $this->createEventDate($event);

        $mapUrlProperty = $this->createEventProperty(
            $event->id,
            EventProperty::PLAY_MAP_URL,
            "https://trackmania.exchange/s/tr/".$map->getId()
        );
        $mapNameProperty = $this->createEventProperty(
            $event->id,
            EventProperty::PLAY_MAP_NAME,
            $map->getName()
        );
        $mapMxIdProperty = $this->createEventProperty(
            $event->id,
            EventProperty::PLAY_MAP_MX_ID,
            $map->getId()
        );
        $hourlyShowdownProperty = $this->createEventProperty(
            $event->id,
            EventProperty::HOURLY_SHOWDOWN,
            true
        );
        $adminProperty = $this->createEventProperty(
            $event->id,
            EventProperty::NON_PARTICIPANT,
            "erwinweber96"
        );
        $adminProperty = $this->createEventProperty(
            $event->id,
            EventProperty::RANKED_EVENT,
            true
        );
        $eventMapPool    = $this->createMapPool($mapPoolProperty, $event);

        CachePlayEventsJob::dispatch();
    }

    private function createEvent(): Event
    {
        $eventBuilder = $this->eventBuilder->setName("Hourly Showdown ".Str::random(6))
            ->setStatusId(Event::STATUS_OPEN)
            ->setAbout("<p>Hourly Showdowns are ranked solo tournaments which are created every hour. What makes this tournament special is the dynamic format. The event is adjusted according to how many players participate. </p>
                            <p>2 Players: 1v1 </p>
                            <p>3 Players: 1v1v1 </p>
                            <p>4 Players: 1v1v1v1 </p>
                            <p>5 Players: Time Attack Quali + Final </p>
                            <p>6-8 Players: Semis + Final </p>
                            <p>9-11 Players: Time Attack Quali + Semis + Final </p>
                            <p>12-16 Players: Quarters + Semis + Final </p>
                            <p>16+ Players: Time Attack Quali + Quarters + Semis + Final</p>")
            ->setGameId(Game::TRACKMANIA_ID)
            ->setIsTeamEvent(false)
            ->setPageId(HourlyEventService::PAGE_ID)
            ->setPrivate(true)
            ->setRegistrationOpen(true)
            ->setRequiredGameAccount(true)
            ->setType("Showdown");

        /** @var Event $event */
        $event = $this->eventRepository->create($eventBuilder);

        return $event;
    }

    private function createEventDate(Event $event): EventDate
    {
        $eventDate = new EventDate();
        $eventDate->name            = EventDate::EVENT_START;
        $eventDate->isStartDate     = true;
        $eventDate->isEndDate       = true;
        $eventDate->isActionDate    = true;
        $eventDate->eventId         = $event->id;

        $date = Carbon::now()
            ->addHour()
            ->setMinutes(0)
            ->setSeconds(0);

        $eventDate->date = $date->toDateTimeString();
        $eventDate->save();
        return $eventDate;
    }

    private function createEventProperty($eventId, $key, $value): EventProperty
    {
        $mapNameProperty = new EventProperty();
        $mapNameProperty->key       = $key;
        $mapNameProperty->value     = $value;
        $mapNameProperty->eventId   = $eventId;
        $mapNameProperty->readOnly  = true;
        $mapNameProperty->save();
        return $mapNameProperty;
    }

    private function createMapPool(PageProperty $mapPoolProperty, Event $event): MapPool
    {
        $mapPool = new MapPool();
        $mapPool->eventId = $event->id;
        $mapPool->name = "Randomly picked map pool";
        $mapPool->link = "https://trackmania.exchange/s/m/".$mapPoolProperty->value;
        $mapPool->mxId = $mapPoolProperty->value;
        $mapPool->save();
        return $mapPool;
    }
}
