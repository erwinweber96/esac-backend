<?php

namespace Modules\Play\Factories;

use Modules\Event\Entities\Event;
use Modules\Group\Entities\Format;
use Modules\Group\Entities\FormatType;
use Modules\Group\Entities\MatchSetting;
use Modules\Match\Entities\MatchEndCondition;
use Modules\Play\Exceptions\NotEnoughParticipants;
use Modules\Play\Exceptions\UnexpectedPlayerCount;

class FormatsFactory
{
    /**
     * @param Event $event
     * @throws NotEnoughParticipants
     * @throws UnexpectedPlayerCount
     * @return Format[]
     */
    public function make(Event $event)
    {
        $numberOfParticipants = $event->participants->count();

        if ($numberOfParticipants <= 1) {
            throw new NotEnoughParticipants();
        }

        if ($numberOfParticipants == 2) {
            //1v1 (rounds 7)
            return [
                $this->createRoundsFormat($event->id)
            ];
        }

        if ($numberOfParticipants == 3) {
            //Final (cup 50)
            return [
                $this->createCupFormat($event->id)
            ];
        }

        if ($numberOfParticipants == 4) {
            //Final (cup 50)
            return [
                $this->createCupFormat($event->id)
            ];
        }

        if ($numberOfParticipants == 5) {
            //TA Quali
            //Final (cup 50)
            return [
                $this->createTimeAttackQualification($event->id),
                $this->createCupFormat($event->id)
            ];
        }

        if ($numberOfParticipants >= 6 && $numberOfParticipants <= 8) {
            //Semi-Final
            return [
                $this->createCupFormat($event->id)
            ];
        }

        if ($numberOfParticipants >= 9 && $numberOfParticipants <= 11) {
            //TA Quali
            //Semi-Final
            return [
                $this->createTimeAttackQualification($event->id),
                $this->createCupFormat($event->id)
            ];
        }

        if ($numberOfParticipants >= 12 && $numberOfParticipants <= 16) {
            //Quarter-Final
            return [
                $this->createCupFormat($event->id)
            ];
        }

        if ($numberOfParticipants >= 16) {
            //TA Quali
            //Quarter Final
            return [
                $this->createTimeAttackQualification($event->id),
                $this->createCupFormat($event->id)
            ];
        }

        throw new UnexpectedPlayerCount();
    }

    /**
     * @param $eventId
     * @return Format
     */
    private function createRoundsFormat($eventId)
    {
        $format = new Format();
        $format->name           = "Rounds format";
        $format->description    = "Rounds format with 7 points limit";
        $format->typeId         = FormatType::ROUNDS_VALUE;
        $format->eventId        = $eventId;
        $format->inheritable    = false;
        $format->areResultsAdditive = true;
        $format->isGameServerGuarded = true;
        $format->matchModifiableByParticipants = false;
        $format->requiresModeratorApproval = true;
        $format->save();

        $matchSetting = new MatchSetting();
        $matchSetting->key      = "S_PointsLimit";
        $matchSetting->value    = "7";
        $matchSetting->formatId = $format->id;
        $matchSetting->save();

        $matchSetting = new MatchSetting();
        $matchSetting->key      = "S_PointsRepartition";
        $matchSetting->value    = "1,0";
        $matchSetting->formatId = $format->id;
        $matchSetting->save();

        $matchSetting = new MatchSetting();
        $matchSetting->key      = "S_WarmUpNb";
        $matchSetting->value    = "1";
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

        $matchEndCondition = new MatchEndCondition();
        $matchEndCondition->minMapsPlayed = 1;
        $matchEndCondition->maxMapsPlayed = 1;
        $matchEndCondition->pointsReached = 7;
        $matchEndCondition->numberOfPlayersWithPointsReached = 1;
        $matchEndCondition->formatId = $format->id;
        $matchEndCondition->save();

        return $format;
    }

    /**
     * @param $eventId
     * @return Format
     */
    private function createTimeAttackQualification($eventId)
    {
        $format = new Format();
        $format->name           = "Time attack qualification format";
        $format->description    = "Time attack qualification format (10 min)";
        $format->typeId         = FormatType::TIME_ATTACK_VALUE;
        $format->eventId        = $eventId;
        $format->inheritable    = false;
        $format->areResultsAdditive = true;
        $format->isGameServerGuarded = true;
        $format->matchModifiableByParticipants = false;
        $format->requiresModeratorApproval = true;
        $format->save();

        $matchSetting = new MatchSetting();
        $matchSetting->key      = "S_TimeLimit";
        $matchSetting->value    = "600";
        $matchSetting->formatId = $format->id;
        $matchSetting->save();

        $matchSetting = new MatchSetting();
        $matchSetting->key      = "S_WarmUpNb";
        $matchSetting->value    = "1";
        $matchSetting->formatId = $format->id;
        $matchSetting->save();

        $matchSetting = new MatchSetting();
        $matchSetting->key      = "S_WarmUpDuration";
        $matchSetting->value    = "300";
        $matchSetting->formatId = $format->id;
        $matchSetting->save();

        $matchEndCondition = new MatchEndCondition();
        $matchEndCondition->minMapsPlayed = 1;
        $matchEndCondition->maxMapsPlayed = 1;
        $matchEndCondition->formatId = $format->id;
        $matchEndCondition->save();

        return $format;
    }

    private function createCupFormat($eventId)
    {
        $format = new Format();
        $format->name           = "Cup format";
        $format->description    = "Cup format with 50 points limit";
        $format->typeId         = FormatType::CUP_VALUE;
        $format->eventId        = $eventId;
        $format->inheritable    = false;
        $format->areResultsAdditive = true;
        $format->isGameServerGuarded = true;
        $format->matchModifiableByParticipants = false;
        $format->requiresModeratorApproval = true;
        $format->save();

        $matchSetting = new MatchSetting();
        $matchSetting->key      = "S_PointsLimit";
        $matchSetting->value    = "50";
        $matchSetting->formatId = $format->id;
        $matchSetting->save();

        $matchSetting = new MatchSetting();
        $matchSetting->key      = "S_WarmUpNb";
        $matchSetting->value    = "1";
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

        $matchSetting = new MatchSetting();
        $matchSetting->key      = "S_NbOfWinners";
        $matchSetting->value    = "2";
        $matchSetting->formatId = $format->id;
        $matchSetting->save();

        $matchSetting = new MatchSetting();
        $matchSetting->key      = "S_PointsRepartition";
        $matchSetting->value    = "10,6,4,3";
        $matchSetting->formatId = $format->id;
        $matchSetting->save();

        $matchEndCondition = new MatchEndCondition();
        $matchEndCondition->minMapsPlayed = 1;
        $matchEndCondition->maxMapsPlayed = 1;
        $matchEndCondition->pointsReached = 51;
        $matchEndCondition->numberOfPlayersWithPointsReached = 2;
        $matchEndCondition->formatId = $format->id;
        $matchEndCondition->save();

        return $format;
    }
}
