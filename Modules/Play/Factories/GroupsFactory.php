<?php

namespace Modules\Play\Factories;

use Illuminate\Support\Facades\Log;
use Modules\Console\Services\DedicatedControllerService;
use Modules\Event\Entities\Event;
use Modules\Event\Entities\EventDate;
use Modules\Event\Entities\EventProperty;
use Modules\Group\Entities\Format;
use Modules\Group\Entities\Group;
use Modules\Group\Entities\GroupType;
use Modules\Map\Entities\MapPool;
use Modules\Map\Entities\MapPoolOrder;
use Modules\Map\ManiaExchange\Entities\Track;
use Modules\Match\Entities\MatchModel;
use Modules\Play\Exceptions\NotEnoughParticipants;
use Modules\Play\Exceptions\UnexpectedPlayerCount;
use Modules\Play\Services\HourlyEventService;
use function Sentry\captureException;

/**
 * Class GroupsFactory
 * @package Modules\Play\Factories
 */
class GroupsFactory
{
    /** @var DedicatedControllerService $dedicatedControllerService */
    private DedicatedControllerService $dedicatedControllerService;

    /**
     * GroupsFactory constructor.
     * @param DedicatedControllerService $dedicatedControllerService
     */
    public function __construct(DedicatedControllerService $dedicatedControllerService)
    {
        $this->dedicatedControllerService = $dedicatedControllerService;
    }

    /**
     * @param Event $event
     * @param Format[] $formats
     * @throws NotEnoughParticipants
     * @throws UnexpectedPlayerCount
     */
    public function make(Event $event, array $formats)
    {
        $numberOfParticipants = $event->participants->count();

        if ($numberOfParticipants < 1) {
            throw new NotEnoughParticipants();
        }

        if ($numberOfParticipants == 2) {
            //1v1 (rounds 7)
            $this->create1v1($event, $formats);
            return;
        }

        if ($numberOfParticipants == 3) {
            //Final (cup 50)
            $this->createFinal($event, $formats, false);
            return;
        }

        if ($numberOfParticipants == 4) {
            //Final (cup 50)
            $this->createFinal($event, $formats, false);
            return;
        }

        if ($numberOfParticipants == 5) {
            //TA Quali
            $this->createTimeAttackQualification($event, $formats);
            //Final (cup 50)
            $this->createFinal($event, $formats, true);
            return;
        }

        if ($numberOfParticipants >= 6 && $numberOfParticipants <= 8) {
            //Semi-Final
            $this->createSemiFinals($event, $formats, false);
            return;
        }

        if ($numberOfParticipants >= 9 && $numberOfParticipants <= 11) {
            //TA Quali
            $this->createTimeAttackQualification($event, $formats);
            //Semi-Final
            $this->createSemiFinals($event, $formats, true);
            return;
        }

        if ($numberOfParticipants >= 12 && $numberOfParticipants <= 16) {
            //Quarter-Final
            $this->createQuarterFinals($event, $formats, false);
            return;
        }

        if ($numberOfParticipants > 16) {
            //TA Quali
            $this->createTimeAttackQualification($event, $formats);
            //Quarter Final
            $this->createQuarterFinals($event, $formats, true);
            return;
        }

        throw new UnexpectedPlayerCount();
    }

    /**
     * @param Event $event
     * @param Format[] $formats
     */
    private function create1v1(Event $event, array $formats)
    {
        $group = new Group();
        $group->name    = HourlyEventService::ONE_VS_ONE_GROUP_NAME;
        $group->eventId = $event->id;
        $group->minSize = 1;
        $group->maxSize = 99;
        $group->type = GroupType::GENERIC;
        $group->save();

        $group->formats()->sync([
            $formats[0]->id
        ]);

        $group->participants()->sync([
            $event->participants[0]->id,
            $event->participants[1]->id
        ]);

        $match = $this->createMatch($event, "1v1 Match", $group->id);
        $match->formats()->sync([
            $formats[0]->id
        ]);
        $match->participants()->sync([
            $event->participants[0]->id,
            $event->participants[1]->id
        ]);

        $this->createMapPoolOrders($event, $match);

        try {
            $this->dedicatedControllerService->startMatch($match->id);
        } catch (\Throwable $exception) {
            \Sentry\captureException($exception);
            $match->statusId = MatchModel::STATUS_ENDED;
            $match->save();
        }
    }

    /**
     * @param Event $event
     * @param array $formats
     * @param bool $withQuali
     */
    private function createFinal(Event $event, array $formats, bool $withQuali)
    {
        $group = new Group();
        $group->name    = HourlyEventService::FINAL_GROUP_NAME;
        $group->eventId = $event->id;
        $group->minSize = 1;
        $group->maxSize = 99;
        $group->type = GroupType::GENERIC;
        $group->save();

        if ($withQuali) {
            $format = $formats[1]->id;
        } else {
            $format = $formats[0]->id;
        }

        $group->formats()->sync([
            $format
        ]);

        $match = $this->createMatch($event, "Cup Mode Match", $group->id);
        $match->formats()->sync([
            $format
        ]);
        $this->createMapPoolOrders($event, $match);

        if (!$withQuali) {
            $participantIds = [];
            foreach ($event->participants as $participant) {
                $participantIds[] = $participant->id;
            }
            $group->participants()->sync($participantIds);
            $match->participants()->sync($participantIds);

            try {
                $this->dedicatedControllerService->startMatch($match->id);
            } catch (\Throwable $exception) {
                \Sentry\captureException($exception);
                $match->statusId = MatchModel::STATUS_ENDED;
                $match->save();
            }
        }

    }

    /**
     * @param Event $event
     * @param array $formats
     * @param bool $withQuali
     */
    private function createSemiFinals(Event $event, array $formats, bool $withQuali)
    {
        $semiFinalsGroup = new Group();
        $semiFinalsGroup->name    = HourlyEventService::SEMI_FINALS_GROUP_NAME;
        $semiFinalsGroup->eventId = $event->id;
        $semiFinalsGroup->minSize = 1;
        $semiFinalsGroup->maxSize = 99;
        $semiFinalsGroup->type = GroupType::GENERIC;
        $semiFinalsGroup->save();

        if ($withQuali) {
            $format = $formats[1]->id;
        } else {
            $format = $formats[0]->id;
        }

        $semiFinalsGroup->formats()->sync([
            $format
        ]);

        $semiFinal1 = $this->createMatch($event, "Semi-Final #1", $semiFinalsGroup->id);
        $semiFinal1->formats()->sync([
            $format
        ]);
        $this->createMapPoolOrders($event, $semiFinal1);

        $semiFinal2 = $this->createMatch($event, "Semi-Final #2", $semiFinalsGroup->id);
        $semiFinal2->formats()->sync([
            $format
        ]);
        $this->createMapPoolOrders($event, $semiFinal2);

        $finalGroup = new Group();
        $finalGroup->name    = HourlyEventService::FINAL_GROUP_NAME;
        $finalGroup->eventId = $event->id;
        $finalGroup->minSize = 1;
        $finalGroup->maxSize = 99;
        $finalGroup->type = GroupType::GENERIC;
        $finalGroup->save();

        $final = $this->createMatch($event, "Final", $finalGroup->id);
        $final->formats()->sync([
            $format
        ]);
        $this->createMapPoolOrders($event, $final);

        if (!$withQuali) {
            $participantIds = [];
            foreach ($event->participants as $participant) {
                $participantIds[] = $participant->id;
            }

            $semiFinalsGroup->participants()->sync($participantIds);
            shuffle($participantIds);
            $chunks = array_chunk($participantIds, ceil(count($participantIds) / 2));

            $semiFinal1->participants()->sync($chunks[0]);
            $semiFinal2->participants()->sync($chunks[1]);

            try {
                $this->dedicatedControllerService->startMatch($semiFinal1->id);
            } catch (\Throwable $exception) {
                \Sentry\captureException($exception);
                $semiFinal1->statusId = MatchModel::STATUS_ENDED;
                $semiFinal1->save();
            }

            try {
                $this->dedicatedControllerService->startMatch($semiFinal2->id);
            } catch (\Throwable $exception) {
                \Sentry\captureException($exception);
                $semiFinal2->statusId = MatchModel::STATUS_ENDED;
                $semiFinal2->save();
            }
        }
    }

    /**
     * @param Event $event
     * @param array $formats
     * @param bool $withQuali
     */
    private function createQuarterFinals(Event $event, array $formats, bool $withQuali)
    {
        $quarterFinals = new Group();
        $quarterFinals->name    = HourlyEventService::QUARTER_FINALS_GROUP_NAME;
        $quarterFinals->eventId = $event->id;
        $quarterFinals->minSize = 1;
        $quarterFinals->maxSize = 99;
        $quarterFinals->type = GroupType::GENERIC;
        $quarterFinals->save();

        if ($withQuali) {
            $format = $formats[1]->id;
        } else {
            $format = $formats[0]->id;
        }

        $quarterFinals->formats()->sync([
            $format
        ]);

        $quarterFinal1 = $this->createMatch($event, "Quarter-Final #1", $quarterFinals->id);
        $quarterFinal1->formats()->sync([
            $format
        ]);
        $this->createMapPoolOrders($event, $quarterFinal1);

        $quarterFinal2 = $this->createMatch($event, "Quarter-Final #2", $quarterFinals->id);
        $quarterFinal2->formats()->sync([
            $format
        ]);
        $this->createMapPoolOrders($event, $quarterFinal2);

        $quarterFinal3 = $this->createMatch($event, "Quarter-Final #3", $quarterFinals->id);
        $quarterFinal3->formats()->sync([
            $format
        ]);
        $this->createMapPoolOrders($event, $quarterFinal3);

        $quarterFinal4 = $this->createMatch($event, "Quarter-Final #4", $quarterFinals->id);
        $quarterFinal4->formats()->sync([
            $format
        ]);
        $this->createMapPoolOrders($event, $quarterFinal4);

        $semiFinals = new Group();
        $semiFinals->name    = HourlyEventService::SEMI_FINALS_GROUP_NAME;
        $semiFinals->eventId = $event->id;
        $semiFinals->minSize = 1;
        $semiFinals->maxSize = 99;
        $semiFinals->type = GroupType::GENERIC;
        $semiFinals->save();

        $semiFinals->formats()->sync([
            $format
        ]);

        $semiFinal1 = $this->createMatch($event, "Semi-Final #1", $semiFinals->id);
        $semiFinal1->formats()->sync([
            $format
        ]);
        $this->createMapPoolOrders($event, $semiFinal1);

        $semiFinal2 = $this->createMatch($event, "Semi-Final #2", $semiFinals->id);
        $semiFinal2->formats()->sync([
            $format
        ]);
        $this->createMapPoolOrders($event, $semiFinal2);

        $final = new Group();
        $final->name    = HourlyEventService::FINAL_GROUP_NAME;
        $final->eventId = $event->id;
        $final->minSize = 1;
        $final->maxSize = 99;
        $final->type = GroupType::GENERIC;
        $final->save();

        $final->formats()->sync([
            $format
        ]);

        $final = $this->createMatch($event, "Final", $final->id);
        $final->formats()->sync([
            $format
        ]);
        $this->createMapPoolOrders($event, $final);

        if (!$withQuali) {
            $participantIds = [];
            foreach ($event->participants as $participant) {
                $participantIds[] = $participant->id;
            }

            $quarterFinals->participants()->sync($participantIds);
            shuffle($participantIds);

            $remainders = [];
            if (count($participantIds) > 12) {
                $split = array_chunk($participantIds, 12);
                $participantIds = $split[0];
                $remainders = $split[1];
            }

            $groups = array_chunk($participantIds, 3);

            if (count($remainders)) {
                foreach ($remainders as $rIndex => $remainder) {
                    foreach($groups as $index => $group) {
                        if (count($group) != 4) {
                            $groups[$index][] = $remainder;
                            break;
                        }
                    }
                }
            }

            $quarterFinal1->participants()->sync($groups[0]);
            $quarterFinal2->participants()->sync($groups[1]);
            $quarterFinal3->participants()->sync($groups[2]);
            $quarterFinal4->participants()->sync($groups[3]);

            try {
                $this->dedicatedControllerService->startMatch($quarterFinal1->id);
            } catch (\Throwable $exception) {
                \Sentry\captureException($exception);
                $quarterFinal1->statusId = MatchModel::STATUS_ENDED;
                $quarterFinal1->save();
            }

            try {
                $this->dedicatedControllerService->startMatch($quarterFinal2->id);
            } catch (\Throwable $exception) {
                \Sentry\captureException($exception);
                $quarterFinal2->statusId = MatchModel::STATUS_ENDED;
                $quarterFinal2->save();
            }

            try {
                $this->dedicatedControllerService->startMatch($quarterFinal3->id);
            } catch (\Throwable $exception) {
                \Sentry\captureException($exception);
                $quarterFinal3->statusId = MatchModel::STATUS_ENDED;
                $quarterFinal3->save();
            }

            try {
                $this->dedicatedControllerService->startMatch($quarterFinal4->id);
            } catch (\Throwable $exception) {
                \Sentry\captureException($exception);
                $quarterFinal4->statusId = MatchModel::STATUS_ENDED;
                $quarterFinal4->save();
            }
        }
    }

    /**
     * @param Event $event
     * @param array $formats
     */
    private function createTimeAttackQualification(Event $event, array $formats)
    {
        $group = new Group();
        $group->name    = HourlyEventService::TIME_ATTACK_QUALIFICATION_GROUP_NAME;
        $group->eventId = $event->id;
        $group->minSize = 1;
        $group->maxSize = 99;
        $group->type = GroupType::GENERIC;
        $group->save();

        $group->formats()->sync([
            $formats[0]->id
        ]);

        $participantIds = [];
        foreach ($event->participants as $participant) {
            $participantIds[] = $participant->id;
        }

        $group->participants()->sync($participantIds);

        $match = $this->createMatch($event, "Time Attack Qualification", $group->id);
        $match->formats()->sync([
            $formats[0]->id
        ]);
        $match->participants()->sync($participantIds);
        $this->createMapPoolOrders($event, $match);

        try {
            $this->dedicatedControllerService->startMatch($match->id);
        } catch (\Throwable $exception) {
            \Sentry\captureException($exception);
            $match->statusId = MatchModel::STATUS_ENDED;
            $match->save();
        }
    }

    /**
     * @param Event $event
     * @param $matchName
     * @param $groupId
     *
     * @return MatchModel
     */
    private function createMatch(Event $event, $matchName, $groupId)
    {
        $match = new MatchModel();
        $match->name = $matchName;
        /** @var EventDate $date */
        $date = $event->dates->where('name', 'event_start')->first();
        $match->date = $date->date;
        $match->mapPoolId = $event->mapPools()->first()->id;
        $match->groupId = $groupId;
        $match->save();
        return $match;
    }

    private function createMapPoolOrders(Event $event, MatchModel $match)
    {
        /** @var EventProperty $mxMapId */
        $mxMapId = $event->properties->where('key', EventProperty::PLAY_MAP_MX_ID)->first();

        /** @var MapPool $mapPool */
        $mapPool = $event->mapPools()->first();

        /** @var Track[] $tracks */
        $tracks = $mapPool->mxData->getTracks();

        foreach ($tracks as $track) {
            $mapPoolOrder = new MapPoolOrder();
            $mapPoolOrder->matchId = $match->id;
            $mapPoolOrder->mapPoolId = $mapPool->id;
            $mapPoolOrder->mxMapId = $track['id'];
            $mapPoolOrder->order = $track['id'] == $mxMapId->value ? 1 : 0;
            $mapPoolOrder->save();
        }
    }
}
