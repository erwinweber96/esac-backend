<?php


namespace Modules\Console\Console\WeeklyTeamEvents;


use Modules\Event\Entities\Event;
use Modules\Event\Entities\Participant;
use Modules\Group\Builders\GroupBuilder;
use Modules\Group\Entities\Group;
use Modules\Group\Repositories\GroupRepository;
use Nwidart\Modules\Collection;

/**
 * Class CreateGroups
 * @package Modules\Console\Console\WeeklyTeamEvents
 *
 * Number of Participants / 4 = number of groups
 * if number of participants % 4 == 1 => -1 number of groups
 * ^ (a group with only one team is cancelled)
 * Sorted by rating. If Rating equal, randomize.
 */
class CreateGroups
{
    private GroupRepository $groupRepository;

    /**
     * CreateGroups constructor.
     * @param GroupRepository $groupRepository
     */
    public function __construct(GroupRepository $groupRepository)
    {
        $this->groupRepository = $groupRepository;
    }

    public function execute(Event $event)
    {
        $numberOfParticipants = $event->participants->count();
        $numberOfGroups       = ceil($numberOfParticipants / 4);
        $participants         = $this->sortParticipants($event->participants);
        $participantsPerGroup = $participants->chunk(4);

        $groups = [];
        for ($i = 1; $i <= $numberOfGroups; $i++) {
            /** @var GroupBuilder $groupBuilder */
            $groupBuilder = app(GroupBuilder::class);

            $groupBuilder->prepare();
            $groupBuilder->setEventId($event->id);
            $groupBuilder->setIsTypeTree(false);
            $groupBuilder->setMaxSize(1);
            $groupBuilder->setMinSize(1);
            $groupBuilder->setName("Group $i");

            /** @var Group $group */
            $group = $this->groupRepository->create($groupBuilder);

            /** @var Participant[]|Collection $groupParticipants */
            $groupParticipants = $participantsPerGroup[$i - 1];
            $groupParticipants = $groupParticipants->shuffle();

            $participantIds = $groupParticipants->map(function (Participant $participant) {
                return $participant->id;
            });
            $participantIds = $participantIds->shuffle();

            $group = $this->groupRepository->syncParticipants($group, $participantIds->toArray());

            $groups[] = $group;
        }

        return $groups;
    }

    /**
     * @param Participant[]|Collection $participants
     */
    private function sortParticipants($participants)
    {
        return $participants->sort(function (Participant $participant1, Participant $participant2) {
           return $participant1->page->elo <= $participant2->page->elo;
        });
    }
}
