<?php


namespace Modules\Map\ManiaExchange\Entities;


use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection;
use Modules\Map\ManiaExchange\Repositories\MappackRepository;
use Modules\Map\ManiaExchange\Repositories\MappackRepositoryInterface;
use Modules\Map\ManiaExchange\Repositories\TMXMappackRepository;

/**
 * Class Mappack
 * @package Modules\Map\ManiaExchange\Entities
 *
 * @property int                    $id                 Internal MX Mappack ID
 * @property string                 $username           Author of the mappack
 * @property string                 $styleName          (e.g. Mixed, Tech, ...)
 * @property string                 $titlePack          Name of the title pack
 * @property string                 $environmentName    (e.g. Stadium, Canyon, ...)
 * @property Track[]|Collection     $tracks             Tracks that belong to this mappack
 * @property string                 $name               Name of the mappack
 */
class Mappack implements Arrayable, Jsonable
{
    private $id;
    private $username;
    private $styleName;
    private $titlePack;
    private $environmentName;
    private $tracks;
    private $name;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername($username): void
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getStyleName()
    {
        return $this->styleName;
    }

    /**
     * @param string $styleName
     */
    public function setStyleName($styleName): void
    {
        $this->styleName = $styleName;
    }

    /**
     * @return string
     */
    public function getTitlePack()
    {
        return $this->titlePack;
    }

    /**
     * @param string $titlePack
     */
    public function setTitlePack($titlePack): void
    {
        $this->titlePack = $titlePack;
    }

    /**
     * @return string
     */
    public function getEnvironmentName()
    {
        return $this->environmentName;
    }

    /**
     * @param string $environmentName
     */
    public function setEnvironmentName($environmentName): void
    {
        $this->environmentName = $environmentName;
    }

    public function getTracks()
    {
        /** @var MappackRepositoryInterface $repository */
        $repository = app(TMXMappackRepository::class);

        /** @var Collection $data */
        $data   = $repository->getTracks($this->getId());

        $tracks = [];
        foreach ($data as $track) {
            $tracks[] = $track->toArray();
        }

        return $tracks;
    }

    public function toArray()
    {
        return [
            "id" => $this->getId(),
            "username" => $this->getUsername(),
            "styleName" => $this->getStyleName(),
            "titlePack" => $this->getTitlePack(),
            "environmentName" => $this->getEnvironmentName(),
            "tracks" => $this->getTracks(),
            "name" => $this->getName()
        ];
    }

    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }
}
