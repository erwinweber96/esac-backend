<?php


namespace Modules\Group\Http\Controllers;


use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Event\Entities\Event;
use Modules\Event\Entities\EventModeratorRole;
use Modules\Event\Entities\Participant;
use Modules\Group\Entities\Group;
use Modules\Group\Entities\GroupContainer;
use Modules\Group\Entities\GroupProperty;
use Modules\Group\Repositories\GroupV2Repository;
use Modules\Map\Entities\Map;
use Modules\Map\Entities\MapPool;
use Modules\Map\Entities\MapPoolOrder;
use Modules\Match\Entities\MatchModel;
use Modules\Match\Entities\MatchProperty;
use Modules\User\Entities\User;

/**
 * Class GroupContainerController
 * @package Modules\Group\Http\Controllers
 */
class GroupContainerController
{
    /** @var GroupV2Repository $groupRepository */
    private $groupRepository;

    /**
     * @param GroupV2Repository $groupRepository
     */
    public function __construct(GroupV2Repository $groupRepository)
    {
        $this->groupRepository = $groupRepository;
    }

    public function get($id)
    {
        /** @var GroupContainer $groupContainer */
        $groupContainer = GroupContainer::where("id", $id)->first();

        $groupContainer->groups = $this->groupRepository->getGroupsByContainer($id);

        return $groupContainer;
    }

    public function create(Request $request)
    {
        /** @var User $user */
        $user = auth()->user();

        $admin = $user->id == 15;

        /** @var Event $event */
        $event = Event::where("id", $request->input("event_id"))->first();

        if ($user->cannot(EventModeratorRole::CREATE_GROUP, [$event]) && !$admin) {
            return response()->json([
                "errors" => [
                    "message" => ["Not Authorized"]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        $groupContainer = new GroupContainer();

        $groupContainer->eventId = $request->input("event_id");
        $groupContainer->name = $request->input("name");
        $groupContainer->typeId = 1;
        $groupContainer->public = $request->input("public");

        $groupContainer->save();

        return $groupContainer;
    }

    public function edit(Request $request)
    {
        /** @var User $user */
        $user = auth()->user();

        $admin = $user->id == 15;

        /** @var GroupContainer $groupContainer */
        $groupContainer = GroupContainer::where("id", $request->input("id"))->first();

        /** @var Event $event */
        $event = Event::where("id", $groupContainer->eventId)->first();

        if ($user->cannot(EventModeratorRole::EDIT_GROUP, [$event]) && !$admin) {
            return response()->json([
                "errors" => [
                    "message" => ["Not Authorized"]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        $groupContainer->name = $request->input("name");
        $groupContainer->public = $request->input("public");

        $groupContainer->save();

        return $groupContainer;
    }

    public function generate(Request $request)
    {
        $type = $request->input("type");
        $participantsPerMatch = $request->input("participantsPerMatch");
        $formatId = $request->input("formatId");
        $mapPoolId = $request->input("mapPoolId");
        $participants = $request->input("participants");
        $seed = $request->input("seed");
        $winnersPerMatch = $request->input("winnersPerMatch");
        $containerId = $request->input("containerId");
        $eventId = $request->input("eventId");

        asort($seed);

        /** @var GroupContainer $groupContainer */
        $groupContainer = GroupContainer::where("id", $containerId)->first();

        switch ($type) {
            case "single-elimination":
                $groupContainer->typeId = GroupContainer::TYPE_SINGLE_ELIMINATION;
                break;
            case "double-elimination":
                $groupContainer->typeId = GroupContainer::TYPE_DOUBLE_ELIMINATION;
                break;
        }

        $groupContainer->save();

        // data
        $numberOfParticipants = count($participants);

        $roundsCounter = 1;
        $roundMatchesMap = [
            $roundsCounter => ceil($numberOfParticipants / $participantsPerMatch)
        ];
        $lbRoundMatchesMap = [];

        do {
            $qualifyToNextRound = $roundMatchesMap[$roundsCounter] * $winnersPerMatch;
            $roundsCounter++;
            $roundMatchesMap[$roundsCounter] = ceil($qualifyToNextRound / $participantsPerMatch);
        } while ($qualifyToNextRound > $participantsPerMatch);

        if ($roundMatchesMap[1] > 8) {
            return response()->json([
                "error" => "Cannot generate more than 8 matches in round 1."
            ], Response::HTTP_BAD_REQUEST);
        }

        $roundsCounter = 1;
        $stackPlayersForNextRound = 0;
        $minusWbRoundsCounter = 0;
        if ($type == 'double-elimination') {

            $expectedLbRounds = 0;
            $counter = 1;
            foreach ($roundMatchesMap as $roundMatchMap) {
                if ($counter % 2 == 0) {
                    if (count($roundMatchesMap) == 2) {
                        $expectedLbRounds++;
                    } else {
                        $expectedLbRounds += 2;
                    }
                } else {
                    $expectedLbRounds++;
                }

                $counter++;
            }

            //TODO: redo logic?
            do {
                if ($stackPlayersForNextRound) {
                    $numberOfAdvancements = $stackPlayersForNextRound;
                    $stackPlayersForNextRound = 0;
                } else {
                    if (!isset($roundMatchesMap[$roundsCounter - $minusWbRoundsCounter])) {
                        $numberOfAdvancements = 0;
                    } else {
                        $numberOfAdvancements = $roundMatchesMap[$roundsCounter - $minusWbRoundsCounter] * $winnersPerMatch;
                    }

                    $minusWbRoundsCounter = 0;
                }

                if (isset($lbRoundMatchesMap[$roundsCounter - 1])) {
                    $lbWinners = $lbRoundMatchesMap[$roundsCounter - 1] * $winnersPerMatch;
                } else {
                    $lbWinners = 0;
                }

                if ($lbWinners > $numberOfAdvancements) {
                    $numberOfPlayersInLB = $lbWinners;
                    $stackPlayersForNextRound = $numberOfAdvancements;
                    $minusWbRoundsCounter++;
                } else {
                    $numberOfPlayersInLB = $numberOfAdvancements + $lbWinners;
                }


                $lbRoundMatchesMap[$roundsCounter] = ceil($numberOfPlayersInLB / $participantsPerMatch);

                $roundsCounter++;
            } while (count($lbRoundMatchesMap) != $expectedLbRounds);
        }

        if ($type == 'double-elimination') {
            $grandFinal = true;
        } else {
            $grandFinal = false;
        }

        /*
         *  In which LB round WB losers end up.
         */

        $wbToLbRoundMap = [
            1 => 1
        ];

        $even = 2;
        for ($i = 2; $i <= count($roundMatchesMap); $i++) {
            $wbToLbRoundMap[$i] = $even;
            $even += 2;
        }

        $wbGroups = [];
        $roundNumberCounter = 1;
        foreach ($roundMatchesMap as $numberOfMatches) {
            //create wb group (name dependent on type - no lb if single elim)
            if ($type == 'double-elimination') {
                $groupName = "WB Round " . $roundNumberCounter;
            } else {
                $groupName = "Round " . $roundNumberCounter;
            }

            $group = $this->createGroup($groupName, $containerId, $eventId);
            $wbGroups[] = $group;

            for ($i = 0; $i < $numberOfMatches; $i++) {
                $matchName = $groupName . " #" . ($i + 1);
                $match = $this->createMatch($matchName, $group->id, $mapPoolId);
                $match->formats()->sync([$formatId]);
            }

            $roundNumberCounter++;
        }

        if ($type == 'double-elimination') {
            $lbGroups = [];
            $roundNumberCounter = 1;
            foreach ($lbRoundMatchesMap as $numberOfMatches) {
                $groupName = "LB Round " . $roundNumberCounter;

                $group = $this->createGroup($groupName, $containerId, $eventId);
                $lbGroups[] = $group;

                for ($i = 0; $i < $numberOfMatches; $i++) {
                    $matchName = $groupName . " #" . ($i + 1);
                    $match = $this->createMatch($matchName, $group->id, $mapPoolId);
                    $match->formats()->sync([$formatId]);
                }

                $roundNumberCounter++;
            }
        }

        foreach ($wbGroups as $wbGroupRoundNumber => $wbGroup) {
            if (!isset($wbGroups[$wbGroupRoundNumber + 1])) {
                break;
            }

            $nextGroup = $wbGroups[$wbGroupRoundNumber + 1];

            $wbGroup->fresh();

            $advanceCounter = 0;
            /** @var MatchModel $match */
            foreach ($wbGroup->matches as $match) {
                for ($i = 0; $i < $winnersPerMatch; $i++) {
                    $matchProperty = new MatchProperty();

                    $matchProperty->matchId = $match->id;
                    $matchProperty->key = MatchProperty::PROMOTION_MATCH_ID;
                    $matchProperty->value = $nextGroup->matches[$advanceCounter]->id;
                    $matchProperty->readOnly = true;

                    $matchProperty->save();

                    $advanceCounter++;

                    if ($advanceCounter > (count($nextGroup->matches) - 1)) {
                        $advanceCounter = 0;
                    }
                }
            }
        }

        if ($type == 'double-elimination') {
            foreach ($lbGroups as $lbGroupRoundNumber => $lbGroup) {
                if (!isset($lbGroups[$lbGroupRoundNumber + 1])) {
                    break;
                }

                $nextGroup = $lbGroups[$lbGroupRoundNumber + 1];

                $advanceCounter = 0;
                $lbGroup->fresh();

                /** @var MatchModel $match */
                foreach ($lbGroup->matches as $match) {
                    for ($i = 0; $i < $winnersPerMatch; $i++) {
                        $matchProperty = new MatchProperty();

                        $matchProperty->matchId = $match->id;
                        $matchProperty->key = MatchProperty::PROMOTION_MATCH_ID;
                        $matchProperty->value = $nextGroup->matches[$advanceCounter]->id;
                        $matchProperty->readOnly = true;

                        $matchProperty->save();

                        $advanceCounter++;
                        if ($advanceCounter > (count($nextGroup->matches) - 1)) {
                            $advanceCounter = 0;
                        }
                    }
                }
            }

            $roundCounter = 1;
            $advanceCounter = 0;
            foreach ($wbGroups as $wbGroup) {
                $demotionRound = $wbToLbRoundMap[$roundCounter];
                $lbGroup = $lbGroups[$demotionRound - 1];

                /** @var MatchModel $match */
                foreach ($wbGroup->matches as $match) {
                    for ($i = 0; $i < $winnersPerMatch; $i++) {
                        $matchProperty = new MatchProperty();

                        $matchProperty->matchId = $match->id;
                        $matchProperty->key = MatchProperty::DEMOTION_MATCH_ID;
                        $matchProperty->value = $lbGroup->matches[$advanceCounter]->id;
                        $matchProperty->readOnly = true;

                        $matchProperty->save();

                        $advanceCounter++;
                        if ($advanceCounter > (count($lbGroup->matches) - 1)) {
                            $advanceCounter = 0;
                        }
                    }
                }

                $roundCounter++;
            }
        }

        if ($grandFinal) {
            $grandFinalGroup = $this->createGroup("Grand Final", $containerId, $eventId);
            $grandFinalMatch = $this->createMatch("Grand Final", $grandFinalGroup->id, $mapPoolId);

            $wbFinal = $wbGroups[count($wbGroups) - 1]->matches[0];

            for ($i = 1; $i <= $winnersPerMatch; $i++) {
                $matchProperty = new MatchProperty();

                $matchProperty->matchId = $wbFinal->id;
                $matchProperty->key = MatchProperty::PROMOTION_MATCH_ID;
                $matchProperty->value = $grandFinalMatch->id;
                $matchProperty->readOnly = true;

                $matchProperty->save();
            }

            $lbFinal = $lbGroups[count($lbGroups) - 1]->matches[0];

            for ($i = 1; $i <= $winnersPerMatch; $i++) {
                $matchProperty = new MatchProperty();

                $matchProperty->matchId = $lbFinal->id;
                $matchProperty->key = MatchProperty::PROMOTION_MATCH_ID;
                $matchProperty->value = $grandFinalMatch->id;
                $matchProperty->readOnly = true;

                $matchProperty->save();
            }
        }

        $firstRound = $wbGroups[0];

        $counter = 0;
        $groupedByMatchId = [];
        foreach ($seed as $participantId => $seedNumber) {
            $groupedByMatchId[$firstRound->matches[$counter]->id][] = (int)$participantId;
            $counter++;
            if ($counter >= count($firstRound->matches)) {
                $counter = 0;
            }
        }

        for ($i = 0; $i < count($firstRound->matches); $i++) {
            $firstRound->matches[$i]->participants()->sync(
                array_values($groupedByMatchId[$firstRound->matches[$i]->id])
            );
        }
    }

    public function delete()
    {
        //not needed?
    }

    private function createGroup($name, $containerId, $eventId)
    {
        $group = new Group();

        $group->name = $name;
        $group->groupContainerId = $containerId;
        $group->eventId = $eventId;

        $group->minSize = 0;
        $group->maxSize = 0;
        $group->type = Group::TYPE_GENERIC;
        $group->isTypeTree = true;
        $group->private = false;

        $group->save();
        return $group;
    }

    private function createMatch($name, $groupId, $mapPoolId)
    {
        $match = new MatchModel();

        $match->name = $name;
        $match->mapPoolId = $mapPoolId;
        $match->groupId = $groupId;
        $match->date = Carbon::now();

        $match->save();

        /** @var MapPool $mapPool */
        $mapPool = MapPool::where("id", $mapPoolId)->first();

        $tracks = $mapPool->mxData->getTracks();
        shuffle($tracks);

        $count = 1;
        foreach ($tracks as $track) {
            $mapPoolOrder = new MapPoolOrder();

            $mapPoolOrder->matchId = $match->id;
            $mapPoolOrder->mapPoolId = $mapPoolId;
            $mapPoolOrder->mxMapId = $track['id'];
            $mapPoolOrder->order = $count;

            $mapPoolOrder->save();
            $count++;
        }

        return $match;
    }

    public function generateRoundRobin(Request $request)
    {
        $participants = $request->input("participants");
        $pointsPerWin = $request->input("pointsPerWin");
        $pointsPerDraw = $request->input("pointsPerDraw");
        $pointsPerLoss = $request->input("pointsPerLoss");
        $eventId = $request->input("eventId");
        $groupContainerId = $request->input("groupContainerId");
        $mapPoolId = $request->input("mapPoolId");
        $formatId = $request->input("formatId");
        $type = $request->input("type");
        $roundDates = $request->input("roundDates");
        $roundTimes = $request->input("roundTimes");

        $participantIds = [];
        foreach ($participants as $participant) {
            $participantIds[] = $participant['value'];
        }

        /** @var GroupContainer $groupContainer */
        $groupContainer = GroupContainer::where("id", $groupContainerId)->first();
        $groupContainer->typeId = $type;
        $groupContainer->save();

        if (count($participantIds) % 2 != 0) {
            array_push($participantIds, "bye");
        }

        $participantPool = $participantIds;
        $rounds = [];
        $away = array_splice($participantPool, (count($participantPool) / 2));
        $home = $participantPool;
        for ($i = 0; $i < count($home) + count($away) - 1; $i++) {
            for ($j = 0; $j < count($home); $j++) {
                $rounds[$i][$j] = [$home[$j], $away[$j]];
            }
            if (count($home) + count($away) - 1 > 2) {
                $offset = 1;
                $length = 1;
                $unshift = array_splice($home, $offset, $length);
                array_unshift($away, array_shift($unshift));
                array_push($home, array_pop($away));
            }
        }

        foreach ($rounds as $roundNumber => $round) {
            $group = new Group();
            $group->name = "Round " . ($roundNumber + 1);
            $group->eventId = $eventId;
            $group->isTypeTree = false;
            $group->minSize = 1;
            $group->maxSize = 1;
            $group->type = Group::TYPE_GENERIC;
            $group->groupContainerId = $groupContainerId;
            $group->save();

            $groupParticipants = $participantIds;

            foreach ($groupParticipants as $gpKey => $groupParticipant) {
                if ($groupParticipant == "bye") {
                    unset($groupParticipants[$gpKey]);
                }
            }

            $group->participants()->sync($groupParticipants);
            $group->save();

            $pointsPerWinProperty = new GroupProperty();
            $pointsPerWinProperty->key = GroupProperty::POINTS_PER_WIN;
            $pointsPerWinProperty->value = $pointsPerWin;
            $pointsPerWinProperty->groupId = $group->id;
            $pointsPerWinProperty->readOnly = false;
            $pointsPerWinProperty->save();


            $pointsPerDrawProperty = new GroupProperty();
            $pointsPerDrawProperty->key = GroupProperty::POINTS_PER_DRAW;
            $pointsPerDrawProperty->value = $pointsPerDraw;
            $pointsPerDrawProperty->groupId = $group->id;
            $pointsPerDrawProperty->readOnly = false;
            $pointsPerDrawProperty->save();


            $pointsPerLossProperty = new GroupProperty();
            $pointsPerLossProperty->key = GroupProperty::POINTS_PER_LOSS;
            $pointsPerLossProperty->value = $pointsPerLoss;
            $pointsPerLossProperty->groupId = $group->id;
            $pointsPerLossProperty->readOnly = false;
            $pointsPerLossProperty->save();

            foreach ($round as $matchNumber => $matchParticipants) {
                //create match
                $match = new MatchModel();
                $match->groupId = $group->id;
                $match->name = "R" . ($roundNumber + 1) . ("M" . ($matchNumber + 1));
                $match->date = new Carbon($roundDates[$roundNumber+1] . " " . $roundTimes[$roundNumber+1]);
                $match->mapPoolId = $mapPoolId;
                $match->save();

                foreach ($matchParticipants as $matchParticipantIndex => $matchParticipant) {
                    if ($matchParticipant == 'bye') {
                        unset($matchParticipants[$matchParticipantIndex]);
                    }
                }

                $match->participants()->sync($matchParticipants);
                $match->formats()->sync([$formatId]);
                $match->save();
            }
        }

        return response()->json(["message" => "Success."], Response::HTTP_OK);
    }

    public function generateSwiss(Request $request)
    {
        $participants = $request->input("participants");
        $groupContainerId = $request->input("groupContainerId");
        $mapPoolId = $request->input("mapPoolId");
        $formatId = $request->input("formatId");
        $roundDate = $request->input("roundDate"); //round 1
        $roundTime = $request->input("roundTime"); //round 1
        $lostUntilElimination = $request->input("lossesUntilElimination");
        $wonUntilQualification = $request->input("winsUntilQualification");
        $seed = $request->input("seed");

        asort($seed);

        /** @var GroupContainer $groupContainer */
        $groupContainer = GroupContainer::where("id", $groupContainerId)->first();
        $groupContainer->typeId = GroupContainer::TYPE_SWISS;
        $groupContainer->save();

        $eventId = $groupContainer->eventId;
        $isTeamEvent = $groupContainer->event->isTeamEvent;
        $participantsPerMatch = $isTeamEvent ? 2 : 4;

        $round1 = new Group();
        $round1->name = "Round 1";
        $round1->eventId = $eventId;
        $round1->isTypeTree = false;
        $round1->minSize = 1;
        $round1->maxSize = 1;
        $round1->type = Group::TYPE_GENERIC;
        $round1->groupContainerId = $groupContainerId;
        $round1->save();

        $wonUntilQualificationProperty = new GroupProperty();
        $wonUntilQualificationProperty->key = GroupProperty::WON_UNTIL_QUALIFICATION;
        $wonUntilQualificationProperty->value = $wonUntilQualification;
        $wonUntilQualificationProperty->groupId = $round1->id;
        $wonUntilQualificationProperty->readOnly = false;
        $wonUntilQualificationProperty->save();

        $lostUntilEliminationProperty = new GroupProperty();
        $lostUntilEliminationProperty->key = GroupProperty::LOST_UNTIL_ELIMINATION;
        $lostUntilEliminationProperty->value = $lostUntilElimination;
        $lostUntilEliminationProperty->groupId = $round1->id;
        $lostUntilEliminationProperty->readOnly = false;
        $lostUntilEliminationProperty->save();

        $seedParticipantIds = [];
        foreach ($seed as $participantId => $seedNumber) {
            $seedParticipantIds[] = $participantId;
        }

        // 1 v 16, 2 v 15, 3 v 14, ...
        $split = (array_chunk($seedParticipantIds, ceil(count($seedParticipantIds)/$participantsPerMatch))); //participantsPerMatch
        //$split[1] = array_reverse($split[1]);

        foreach ($split as $i => $chunk) {
            if ($i % 2 != 0) {
                $split[$i] = array_reverse($split[$i]);
            }
        }

        foreach ($split[0] as $i => $participant1Id) {
            $matchParticipants = [$participant1Id];

//            if (isset($split[1][$i])) { //participantsPerMatch
//                $matchParticipants[] = $split[1][$i];
//            }

            foreach ($split as $j => $chunk) {
                if ($j == 0) {
                    continue;
                }

                if (isset($chunk[$i])) {
                    $matchParticipants[] = $chunk[$i];
                }
            }

            $match = new MatchModel();
            $match->groupId = $round1->id;
            $match->name = "M".($i+1)." R1";
            $match->date = new Carbon($roundDate . " " . $roundTime);
            $match->mapPoolId = $mapPoolId;
            $match->save();

            $match->participants()->sync($matchParticipants);
            $match->formats()->sync([$formatId]);
            $match->save();
        }
    }

    public function generateNextSwissRound(Request $request)
    {
        //TODO: throw if not all matches ended

        $groupContainerId = $request->input("groupContainerId");

        /** @var Group[]|Collection $groups */
        $groups = Group::where("group_container_id", $groupContainerId)->get();

        $isTeamEvent = $groups[0]->event->isTeamEvent;
        $participantsPerMatch = $isTeamEvent ? 2 : 4;

        foreach ($groups as $group) {
            foreach ($group->matches as $match) {
                if ($match->statusId != MatchModel::STATUS_ENDED) {
                    return response()->json(["error" => [
                        "messages" => ["All matches have to be ended before generating next round."]
                    ]], Response::HTTP_BAD_REQUEST);
                }
            }
        }

        /** @var GroupProperty $wonUntilQuali */
        $wonUntilQuali = $groups[0]
            ->properties
            ->where("key", GroupProperty::WON_UNTIL_QUALIFICATION)
            ->first();

        /** @var GroupProperty $lostUntilElim */
        $lostUntilElim = $groups[0]
            ->properties
            ->where("key", GroupProperty::LOST_UNTIL_ELIMINATION)
            ->first();

        $event = $groups[0]->event;

        $wonLostMap = [];
        foreach ($event->participants as $participant) {
            $participantMatches = $participant->matches->filter(function (MatchModel $match) use ($groupContainerId, $isTeamEvent){
                return $match->group->groupContainerId == $groupContainerId;
            });

            $wonMatches = $participantMatches->filter(function (MatchModel $match) use ($participant, $isTeamEvent) {
                if ($match->totalMatchResults[$participant->id] == null) {
                    return false;
                }

                $participantResult = $match->totalMatchResults[$participant->id]->result;

                $resultsOnly = [];
                foreach ($match->totalMatchResults as $totalMatchResult) {
                    if ($totalMatchResult == null) {
                        continue;
                    }

                    if ($isTeamEvent) {
                        if ($totalMatchResult->result > $participantResult) {
                            return false;
                        }
                    } else {
                        $resultsOnly[] = $totalMatchResult->result;
                    }
                }

                if (count($resultsOnly) && !$isTeamEvent) {
                    rsort($resultsOnly);

                    //is in top 2
                    return $resultsOnly[0] == $participantResult || $resultsOnly[1] == $participantResult;
                }

                return true;
            });

            $wonMatchesCount  = $wonMatches->count();
            $lostMatchesCount = $participantMatches->count() - $wonMatchesCount;

            if ($lostMatchesCount >= $lostUntilElim->value) {
                $eliminated = new GroupProperty();

                $eliminated->key = GroupProperty::ELIMINATED;
                $eliminated->value = $participant->id;
                $eliminated->groupId = $groups->last()->id;
                $eliminated->readOnly = false;

                $eliminated->save();

                continue;
            }

            if ($wonMatchesCount >= $wonUntilQuali->value) {
                $qualified = new GroupProperty();

                $qualified->key = GroupProperty::QUALIFIED;
                $qualified->value = $participant->id;
                $qualified->groupId = $groups->last()->id;
                $qualified->readOnly = false;

                $qualified->save();

                continue;
            }

            $wonLostMap[$wonMatchesCount."W".$lostMatchesCount."L"][] = $participant;
        }

        if (count($wonLostMap) == 0) {
            //TODO: send json -> event ended
            return response()->json(["error" => [
                "messages" => ["No extra swiss rounds needed. This step is over."]
            ]], Response::HTTP_BAD_REQUEST);
        }

        $nextRound = new Group();
        $nextRound->name = "Round " . ($groups->count() + 1);
        $nextRound->eventId = $event->id;
        $nextRound->isTypeTree = false;
        $nextRound->minSize = 1;
        $nextRound->maxSize = 1;
        $nextRound->type = Group::TYPE_GENERIC;
        $nextRound->groupContainerId = $groupContainerId;
        $nextRound->save();

        foreach ($wonLostMap as $key => $participants) {
            $participants = collect($participants);
            $participantIds = $participants->map(function (Participant $participant) {
                return $participant->id;
            });
            $participantIds = ($participantIds->toArray());

            $matchParticipants = array_chunk($participantIds, $participantsPerMatch); //participantsPerMatch
            $numberOfMatches = count($matchParticipants);

            for ($i = 1; $i <= $numberOfMatches; $i++) {
                $match = new MatchModel();
                $match->groupId = $nextRound->id;
                $match->name = $key . " #" . $i;
                $match->date = new Carbon();
                $match->mapPoolId = $groups[0]->matches->first()->mapPoolId;
                $match->save();

                $match->participants()->sync($matchParticipants[$i-1]);
                $match->formats()->sync([$groups[0]->matches->first()->formats->first()->id]);
                $match->save();
            }
        }

        return response()->json(["message" => "Success."]);
    }
}
