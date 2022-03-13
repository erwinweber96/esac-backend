<?php


namespace Modules\Console\Scheduler\Actions;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\Console\Entities\ScheduledActionFilter;
use Modules\Console\Scheduler\Filters\AbstractFilter;

/**
 * Class ScheduledActionHandler
 * @package Modules\Console\Scheduler\Actions
 */
abstract class ScheduledActionHandler
{
    /** @var ScheduledActionFilter[]|Collection $filters */
    private $filters;

    /**
     * @param ScheduledActionHandlerData $handlerData
     */
    public function setData(ScheduledActionHandlerData $handlerData)
    {
        $data = $handlerData->getData();

        foreach ($this as $key => &$value) {
            if ($key == "filters") {
                continue;
            }

            $value = $data[$key];
        }
    }


    /**
     * @return Collection|ScheduledActionFilter[]
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param Collection|ScheduledActionFilter[] $filters
     */
    public function setFilters($filters): void
    {
        $this->filters = $filters;
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    protected function applyFilters(Builder $builder): Builder
    {
        foreach ($this->getFilters() as $filter) {
            /** @var AbstractFilter $filterInstance */
            $filterInstance = app($filter->class);
            $builder = $filterInstance->filter($builder);
        }

        return $builder;
    }

    abstract public function run();
}
