<?php


namespace Modules\Console\Entities;


use Illuminate\Contracts\Support\Arrayable;

/**
 * Class GenericEventData
 * @package Modules\Console\Entities
 *
 * @property string $channel
 * @property array  $data
 * @property string $name
 */
class GenericEventData implements \JsonSerializable
{
    /** @var string $channel */
    private $channel;

    /** @var array $data */
    private $data;

    /** @var string $name */
    private $name;

    /**
     * @return string
     */
    public function getChannel(): string
    {
        return $this->channel;
    }

    /**
     * @param string $channel
     */
    public function setChannel(string $channel): void
    {
        $this->channel = $channel;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

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


    public function jsonSerialize()
    {
        return $this->data;
    }
}
