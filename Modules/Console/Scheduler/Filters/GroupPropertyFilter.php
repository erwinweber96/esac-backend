<?php


namespace Modules\Console\Scheduler\Filters;


use Illuminate\Database\Eloquent\Builder;
use Modules\Group\Entities\Group;

/**
 * Class GroupPropertyFilter
 * @package Modules\Console\Scheduler\Filters
 */
class GroupPropertyFilter extends AbstractFilter
{
    public string $key;
    public string $value;

    public function filterModel(): string
    {
        return Group::class;
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
