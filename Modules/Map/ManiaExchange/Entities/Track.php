<?php


namespace Modules\Map\ManiaExchange\Entities;


use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

/**
 * Class Track
 * @package Modules\Map\ManiaExchange\Entities
 *
 * @hidden int        $id                 Internal MX Track ID
 * @hidden string     $username           Author of the map
 * @hidden string     $titlePack          Name of the title pack
 * @hidden string     $environmentName    (e.g. Stadium, Canyon, ...)
 * @hidden string     $trackUid           Internal MX Track Unique ID
 * @hidden string     $name               Name of the track
 */
class Track implements Arrayable, Jsonable, \JsonSerializable
{
    private $id;
    private $username;
    private $titlePack;
    private $environmentName;
    private $trackUid;
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
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id): void
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

    /**
     * @return string
     */
    public function getTrackUid()
    {
        return $this->trackUid;
    }

    /**
     * @param string $trackUid
     */
    public function setTrackUid($trackUid): void
    {
        $this->trackUid = $trackUid;
    }

    public function toArray()
    {
        return [
            "id" => $this->getId(),
            "username" => $this->getUsername(),
            "titlePack" => $this->getTitlePack(),
            "environmentName" => $this->getEnvironmentName(),
            "trackUid" => $this->getTrackUid(),
            "name" => $this->getName()
        ];
    }

    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
