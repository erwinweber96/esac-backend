<?php


namespace Modules\Console\Events\Actions;


use Modules\Console\Events\MatchEnded;
use Modules\Event\Entities\EventProperty;
use Modules\Group\Entities\Group;
use Modules\Group\Entities\GroupResult;
use Modules\Match\Entities\MatchModel;
use Modules\Match\Entities\MatchResult;

/**
 * Class HandleMultiMapSeedingPhase
 * @package Modules\Console\Events\Actions
 */
class HandleMultiMapSeedingPhase
{
    public function handle(MatchEnded $trigger)
    {
        try {
            $match = $trigger->match;

            $eventId = $match->group->eventId;

            /** @var Group $firstGroup */
            $firstGroup = Group::where("event_id", $eventId)->first();

            if ($match->groupId != $firstGroup->id) {
                // If not a seeding phase match
                return;
            }

            $multiMapSeedingPhase = $match
                ->group
                ->event
                ->properties
                ->filter(function (EventProperty $property) {
                    return $property->key == EventProperty::MULTI_MAP_SEEDING_PHASE;
                });

            if (!$multiMapSeedingPhase->count()) {
                return;
            }

            //Just to be sure.
            $match->statusId = MatchModel::STATUS_ENDED;
            $match->save();

            /** @var Group $group */
            $group = Group::where("type", Group::TYPE_RESULT)
                ->where("event_id", $match->group->eventId)
                ->first();

            if (!$group) {
                $group = new Group();

                $group->name = "Seeding Phase Results";
                $group->minSize = 1;
                $group->maxSize = 1;
                $group->isTypeTree = false;
                $group->eventId = $match->group->eventId;
                $group->type = Group::TYPE_RESULT;
                $group->private = true;

                $group->save();
                $previousGroupResults = false;
            } else {
                $previousGroupResults = GroupResult::where("group_id", $group->id)->get();
                GroupResult::where("group_id", $group->id)->delete();
            }

            $event = $group->event;

            $matchResults = collect($match->totalMatchResults);

            $matchResults = $matchResults->map(function ($matchResult, $participantId) {
                if (!isset($matchResult)) {
                    $matchResult = new \stdClass();
                    $matchResult->participantId = $participantId;
                }
                if (!isset($matchResult->result)) {
                    $matchResult->result = 300000;
                } else {
                    if ($matchResult->result == 0) {
                        $matchResult->result = 300000;
                    }
                }

                return $matchResult;
            });

            $matchResults = $matchResults->sort(function ($matchResult1, $matchResult2) {
                return $matchResult1->result >= $matchResult2->result;
            });

            $position = 1;
            /** @var MatchResult $matchResult */
            foreach ($matchResults as $matchResult) {
                $time = (int)$matchResult->result;

                if ($previousGroupResults) {
                    /** @var GroupResult $previousTime */
                    $previousTime = $previousGroupResults
                        ->where("participant_id", $matchResult->participantId)
                        ->first();

                    if ($previousTime) {
                        $time += (int)$previousTime->result;
                    }
                }

                $groupResult = new GroupResult();

                $groupResult->groupId = $group->id;
                $groupResult->participantId = $matchResult->participantId;
                $groupResult->position = $position++;
                $groupResult->prize = "0";
                $groupResult->result = $time;

                $groupResult->save();
            }

            if ($previousGroupResults) {
                /** @var GroupResult $previousGroupResult */
                foreach ($previousGroupResults as $previousGroupResult) {
                    if (!$matchResults->where("participant_id", $previousGroupResult->participantId)->count()) {
                        $groupResult = new GroupResult();

                        $groupResult->groupId = $group->id;
                        $groupResult->participantId = $previousGroupResult->participantId;
                        $groupResult->position = $previousGroupResult->position;
                        $groupResult->prize = "0";
                        $groupResult->result = $previousGroupResult->result;

                        $groupResult->save();
                    }
                }

                //reindex
                $allGroupResults = GroupResult::where("group_id", $group->id)->get();
                $allGroupResults = $allGroupResults->sort(function ($matchResult1, $matchResult2) {
                    return $matchResult1->result >= $matchResult2->result;
                });

                $i = 1;
                /** @var GroupResult $oneGroupResult */
                foreach ($allGroupResults as $oneGroupResult) {
                    $oneGroupResult->position = $i;
                    $i++;

                    $oneGroupResult->save();
                }
            }

            $endedMatches = $group->matches->filter(function (MatchModel $match) {
                return $match->statusId == MatchModel::STATUS_ENDED;
            });

            if ($endedMatches->count() != $group->matches->count()) {
                // Seeding phase not done yet.
                return;
            }

            //Seed brackets

            /** @var EventProperty $numberOfQualifiedPlayers */
            $numberOfQualifiedPlayers = $group
                ->event
                ->properties
                ->where("key", EventProperty::NUMBER_OF_QUALIFIED_PLAYERS)
                ->first();

            /** @var GroupResult[] $seedingResults */
            $seedingResults = GroupResult::where("group_id", $group->id)
                ->orderBy("position")
                ->get();

            $playersPerMatch = 4; //TODO: this is only compatible with 1v1v1v1
            $numberOfMatches = (int)$numberOfQualifiedPlayers->value / $playersPerMatch;
            $round1 = $event->groups[1];

            for ($i = 0; $i < $numberOfMatches; $i++) {
                $participantIds = [];

                for ($j = $i; $j < (int)$numberOfQualifiedPlayers->value; $j += $numberOfMatches) {
                    if (isset($seedingResults[$j])) {
                        $participantIds[] = $seedingResults[$j]->participantId;
                    }
                }

                $round1->matches[$i]->participants()->sync($participantIds);
            }
        } catch (\Throwable $exception) {
            dd($exception->getMessage());
        }
    }
}
