<?php


namespace Modules\Match\Builders;


use App\Model\Builder;

/**
 * Class MatchResultBuilder
 * @package Modules\Match\Builders
 */
class MatchResultBuilder implements Builder
{
    /** @var array $match */
    private $match;

    public function prepare(): Builder
    {
        $this->match = [];
        return $this;
    }

    public function build()
    {
        return $this->match;
    }

    public function setMatchId(int $matchId): self
    {
        $this->match['match_id'] = $matchId;
        return $this;
    }

    public function setResult(string $result): self
    {
        $this->match['result'] = $result;
        return $this;
    }

    public function setIsTotalResult(bool $isTotalResult): self
    {
        $this->match['is_total_result'] = $isTotalResult;
        return $this;
    }

    public function setParticipantId(int $participantId): self
    {
        $this->match['participant_id'] = $participantId;
        return $this;
    }

    public function setPending(bool $pending): self
    {
        $this->match['pending'] = $pending;
        return $this;
    }

    public function setMapId(int $mapId)
    {
        $this->match['map_id'] = $mapId;
        return $this;
    }
}
