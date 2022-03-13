<?php


namespace Modules\Link\Builders;


use App\Model\Builder;

/**
 * Class LinkBuilder
 * @package Modules\Link\Builders
 */
class LinkBuilder implements Builder
{
    /** @var array $link */
    private $link;

    public function prepare(): Builder
    {
        $this->link = [];
        return $this;
    }

    public function build()
    {
        return $this->link;
    }

    public function setUrl(string $url): self
    {
        $this->link['url'] = $url;
        return $this;
    }

    public function setName(string $name): self
    {
        $this->link['name'] = $name;
        return $this;
    }

    public function setPending(bool $pending): self
    {
        $this->link['pending'] = $pending;
        return $this;
    }
}
