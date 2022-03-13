<?php


namespace Modules\Link\Builders;


use App\Model\Builder;

/**
 * Class LiveStreamBuilder
 * @package Modules\Link\Builders
 */
class LiveStreamBuilder implements Builder
{
    /** @var array $liveStream */
    private $liveStream;

    public function prepare(): Builder
    {
        $this->liveStream = [];
        return $this;
    }

    public function build()
    {
        return $this->liveStream;
    }

    public function setLinkId(int $linkId): self
    {
        $this->liveStream['link_id'] = $linkId;
        return $this;
    }

    public function setMatchId(int $matchId): self
    {
        $this->liveStream['match_id'] = $matchId;
        return $this;
    }
}
