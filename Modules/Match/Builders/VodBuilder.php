<?php


namespace Modules\Match\Builders;


use App\Model\Builder;

class VodBuilder implements Builder
{
    /** @var array $vod */
    private $vod;

    public function prepare(): Builder
    {
        $this->vod = [];
        return $this;
    }

    public function build()
    {
        return $this->vod;
    }

    public function setAbout(string $about): self
    {
        $this->vod['about'] = $about;
        return $this;
    }

    public function setLinkId(int $linkId): self
    {
        $this->vod['link_id'] = $linkId;
        return $this;
    }

    public function setMatchId(int $matchId): self
    {
        $this->vod['match_id'] = $matchId;
        return $this;
    }
}
