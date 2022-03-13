<?php


namespace Modules\Console\Scheduler\Actions;


use Modules\Group\Entities\Group;
use Modules\Match\Builders\MatchBuilder;
use Modules\Match\Repositories\MatchRepository;

/**
 * Class CreateMatch
 * @package Modules\Console\Scheduler\Actions
 */
class CreateMatch extends ScheduledActionHandler
{
    public int    $groupId;
    public int    $eventId;
    public string $matchName;
    public string $date;
    public string $time;
    public int    $mapPoolId;

    public function run()
    {
        if (!$this->groupId) {
            $groupQuery = Group::where("id", $this->groupId);
            $groupQuery = $this->applyFilters($groupQuery);

            /** @var Group $group */
            $group = $groupQuery->first();
            $this->groupId = $group->id;
        }

        $matchBuilder = new MatchBuilder();

        /** @var MatchBuilder $matchBuilder */
        $matchBuilder = $matchBuilder->prepare();

        $matchBuilder
            ->setName($this->matchName)
            ->setEventId($this->eventId)
            ->setDate($this->date)
            ->setTime($this->time)
            ->setGroupId($this->groupId)
            ->setMapPoolId($this->mapPoolId);

        /** @var MatchRepository $matchRepository */
        $matchRepository = app(MatchRepository::class);

        $matchRepository->create($matchBuilder);
    }
}
