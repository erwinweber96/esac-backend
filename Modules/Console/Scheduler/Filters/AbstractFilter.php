<?php


namespace Modules\Console\Scheduler\Filters;


use Illuminate\Database\Eloquent\Builder;

/**
 * Class AbstractFilter
 * @package Modules\Console\Scheduler\Filters
 */
abstract class AbstractFilter
{
    /**
     * @param FilterHandlerData $handlerData
     */
    public function setData(FilterHandlerData $handlerData)
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
     * Returns the model of the builder which is being filtered.
     *
     * @return string
     */
    abstract public function filterModel(): string;

    /**
     * Adds queries to the Builder to filter in/out what is needed for the action.
     *
     * @param Builder $builder
     * @return Builder
     */
    abstract public function filter(Builder $builder): Builder;
}
