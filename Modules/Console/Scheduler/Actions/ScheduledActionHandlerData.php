<?php


namespace Modules\Console\Scheduler\Actions;


/**
 * Class ScheduledActionHandlerData
 * @package Modules\Console\Scheduler\Actions
 */
class ScheduledActionHandlerData
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
