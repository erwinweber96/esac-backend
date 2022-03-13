<?php


namespace Modules\GameServer\Builders;


use App\Model\Builder;

/**
 * Class GameServerBuilder
 * @package Modules\GameServer\Builders
 */
class GameServerBuilder implements Builder
{
    /** @var array $gameServer */
    private $gameServer;

    public function prepare(): Builder
    {
        $this->gameServer = [];
        return $this;
    }

    public function build()
    {
        return $this->gameServer;
    }

    public function setName(string $name): self
    {
        $this->gameServer['name'] = $name;
        return $this;
    }

    public function setUrl(string $url): self
    {
        $this->gameServer['url'] = $url;
        return $this;
    }

    public function setPassword(string $password): self
    {
        $this->gameServer['password'] = $password;
        return $this;
    }

    public function setPending(bool $pending): self
    {
        $this->gameServer['pending'] = $pending;
        return $this;
    }
}
