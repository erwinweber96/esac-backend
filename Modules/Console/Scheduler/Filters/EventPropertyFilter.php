<?php


namespace Modules\Console\Scheduler\Filters;


use Illuminate\Database\Eloquent\Builder;
use Modules\Event\Entities\Event;

/**
 * Class EventPropertyFilter
 * @package Modules\Console\Scheduler\Filters
 */
class EventPropertyFilter extends AbstractFilter
{
    public string $key;
    public string $value;

    public function filterModel(): string
    {
        return Event::class;
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    public function filter(Builder $builder): Builder
    {
        $builder->with(['properties'], function ($query) {
            $query
                ->where('key', '=', $this->key)
                ->where('value', '=', $this->value);
        });

        return $builder;
    }
}
