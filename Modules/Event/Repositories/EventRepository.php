<?php


namespace Modules\Event\Repositories;


use App\Model\Builder;
use App\Repository\Repository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Modules\Event\Entities\Event;

/**
 * Class EventRepository
 * @package Modules\Event\Repositories
 */
class EventRepository implements Repository
{
    /** @var Event $event */
    private $event;

    /**
     * EventRepository constructor.
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    public function all(): Collection
    {
        //TODO:
    }

    public function create(Builder $data): Model
    {
        return $this->event->create($data->build());
    }

    public function update(Builder $data, $id): bool
    {
        return $this->event->where("id", $id)->update($data->build());
    }

    public function delete($id): bool
    {
        // TODO: Implement delete() method.
    }

    public function show($id): Model
    {
        return $this->event->where("id", $id)->first();
    }

    /**
     * @param $slug
     * @return Event
     */
    public function findBySlug($slug): Model
    {
        $event = $this->event
            ->where("slug", $slug)
            ->with($this->event->relations)
            ->with('formats.type')
            ->with('formats.matchEndCondition')
            ->with('formats.matchSettings')
            ->with('groups.formats')
            ->with('participants.user')
            ->with('participants.page')
            ->with('participants.page.members')
            ->with('participants.page.members.user')
            ->with('participants.page.user')
            ->with('participants.user.discord')
            ->with('groups.matches.formats')
            ->first();

        if (!$event) {
            $event = $this->event
                ->where("id", $slug)
                ->with($this->event->relations)
                ->with('formats.type')
                ->with('formats.matchEndCondition')
                ->with('formats.matchSettings')
                ->with('groups.formats')
                ->with('participants.user')
                ->with('participants.page')
                ->with('participants.page.members')
                ->with('participants.page.members.user')
                ->with('participants.page.user')
                ->with('participants.user.discord')
                ->with('groups.matches.formats')
                ->first();
        }

        return $event;
    }
}
