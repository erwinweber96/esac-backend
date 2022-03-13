<?php


namespace Modules\Console\Scheduler\Filters;


/**
 * Class FilterHandlerData
 * @package Modules\Console\Scheduler\Filters
 */
class FilterHandlerData
{
    public array $data = [];

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param string $data
     */
    public function setData(string $data): void
    {
        $this->data = json_decode($data, true);
    }
}
