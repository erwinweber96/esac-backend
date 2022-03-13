<?php


namespace Modules\Console\Scheduler\Filters;


use Illuminate\Database\Eloquent\Builder;
use Modules\Event\Entities\Participant;

/**
 * Class SortEventParticipantsByTeamRank
 * @package Modules\Console\Scheduler\Filters
 */
class SortEventParticipantsByTeamRank extends AbstractFilter
{
    public function filterModel(): string
    {
        return Participant::class;
    }

    public function filter(Builder $builder): Builder
    {
        return $builder->orderBy("page.elo", "desc");
    }
}
