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
use Modules\Group\Entities\Format;
use Modules\Group\Entities\FormatType;
use Modules\Group\Entities\Group;
use Modules\Group\Entities\MatchSetting;
use Modules\Map\Entities\MapPool;
use Modules\Map\ManiaExchange\Repositories\TMXMappackRepository;
use Modules\Match\Entities\MatchEndCondition;
use Modules\Page\Entities\PageProperty;
use Modules\Play\Jobs\CachePlayEventsJob;

class CreateDailyMatchmakingLadders extends Command
{
    const COMMAND = "matchmaking_ladders:create";

    const PAGE_ID = 283;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = self::COMMAND;

    public function handle()
    {
        // get each map pool from page properties
        $mapPools = PageProperty::where("key", PageProperty::MATCHMAKING_MX_POOL)
            ->where("page_id", self::PAGE_ID)
            ->get();

        $events = [];

        /** @var PageProperty $mapPool */
        foreach ($mapPools as $mapPool) {
            $mxId = $mapPool->value;

            /** @var TMXMappackRepository $mappackRepository */
            $mappackRepository = app(TMXMappackRepository::class);

            $mappack = $mappackRepository->findById($mxId);

            // Create event
            /** @var EventBuilder $eventBuilder */
            $eventBuilder = app(EventBuilder::class);

            /** @var EventBuilder $eventBuilder */
            $eventBuilder = $eventBuilder->prepare();

            $eventBuilder->setName($mappack->getName(), false)
                ->setSlug($mappack->getName()." ".Str::random(8))
                ->setAbout("Daily Matchmaking Ladder on ".$mappack->getName()." maps.")
                ->setIsTeamEvent(false)
                ->setType("Matchmaking Ladder")
                ->setPageId(self::PAGE_ID)
                ->setPrivate(true)
                ->setRequiredGameAccount(true)
                ->setGameId(Game::TRACKMANIA_ID);

            /** @var EventRepository $eventRepository */
            $eventRepository = app(EventRepository::class);

            /** @var Event $event */
            $event = $eventRepository->create($eventBuilder);
            $events[] = $event;

            $startDate = Carbon::now('CET');
            $startDate->subHour(1);
            $eventStartDate = new EventDate();

            $eventStartDate->date         = $startDate;
            $eventStartDate->eventId      = $event->id;
            $eventStartDate->isStartDate  = true;
            $eventStartDate->isEndDate    = false;
            $eventStartDate->isActionDate = false;
            $eventStartDate->name         = EventDate::EVENT_START;

            $eventStartDate->save();

            $endDate      = $startDate->addHours(24);
            $eventEndDate = new EventDate();

            $eventEndDate->date         = $endDate;
            $eventEndDate->eventId      = $event->id;
            $eventEndDate->isStartDate  = true;
            $eventEndDate->isEndDate    = false;
            $eventEndDate->isActionDate = false;
            $eventEndDate->name         = EventDate::EVENT_END;

            $eventEndDate->save();

            // Map pool
            $mapPool = new MapPool();

            $mapPool->mxId = $mxId;
            $mapPool->eventId = $event->id;
            $mapPool->name = $mappack->getName();

            $mapPool->save();

            // Format
            $format = new Format();

            $format->name = "1v1v1v1 Matchmaking";
            $format->eventId = $event->id;
            $format->typeId = FormatType::CUP_VALUE;
            $format->inheritable = false;
            $format->areResultsAdditive = false;
            $format->isGameServerGuarded = false;
            $format->matchModifiableByParticipants = false;
            $format->requiresModeratorApproval = false;
            $format->description = "1v1v1v1 Matchmaking";

            $format->save();

            // Matchsettings
            $matchSetting = new MatchSetting();
            $matchSetting->key      = "S_PointsLimit";
            $matchSetting->value    = "50";
            $matchSetting->formatId = $format->id;
            $matchSetting->save();

            $matchSetting = new MatchSetting();
            $matchSetting->key      = "S_PointsRepartition";
            $matchSetting->value    = "10,6,4,3";
            $matchSetting->formatId = $format->id;
            $matchSetting->save();

            $matchSetting = new MatchSetting();
            $matchSetting->key      = "S_WarmUpNb";
            $matchSetting->value    = "1";
            $matchSetting->formatId = $format->id;
            $matchSetting->save();

            $matchSetting = new MatchSetting();
            $matchSetting->key      = "S_NbOfWinners";
            $matchSetting->value    = "2";
            $matchSetting->formatId = $format->id;
            $matchSetting->save();

            $matchSetting = new MatchSetting();
            $matchSetting->key      = "S_WarmUpDuration";
            $matchSetting->value    = "300";
            $matchSetting->formatId = $format->id;
            $matchSetting->save();

            $matchSetting = new MatchSetting();
            $matchSetting->key      = "S_RoundsPerMap";
            $matchSetting->value    = "999";
            $matchSetting->formatId = $format->id;
            $matchSetting->save();

            // Match end conditions
            $matchEndCondition = new MatchEndCondition();
            $matchEndCondition->minMapsPlayed = 1;
            $matchEndCondition->maxMapsPlayed = 1;
            $matchEndCondition->pointsReached = 51;
            $matchEndCondition->numberOfPlayersWithPointsReached = 2;
            $matchEndCondition->formatId = $format->id;
            $matchEndCondition->save();

            // Group
            $group = new Group();

            $group->eventId = $event->id;
            $group->name = "Matchmaking matches";
            $group->minSize = 1;
            $group->maxSize = 999;
            $group->type = Group::TYPE_GENERIC;
            $group->isTypeTree = false;
            $group->private = false;

            $group->save();

            // Properties
            EventProperty::create([
                "event_id"  => $event->id,
                "key"       => EventProperty::MATCHMAKING_LADDER,
                "value"     => true,
                "read_only" => true
            ]);

            EventProperty::create([
                "event_id"  => $event->id,
                "key"       => EventProperty::RANKED_EVENT,
                "value"     => true,
                "read_only" => true
            ]);
        }

        if (count($events)) {
            $randomEvent = rand(0,count($events)-1);
            $randomEventId = $events[$randomEvent]->id;

            EventProperty::create([
                "event_id"  => $randomEventId,
                "key"       => EventProperty::BADGE_ID_1131_ACHIEVEMENT,
                "value"     => true,
                "read_only" => true
            ]);
        }

        CachePlayEventsJob::dispatch();
    }
}
