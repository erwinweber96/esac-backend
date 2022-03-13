<?php


namespace Modules\Event\Repositories;


use App\Model\Builder;
use App\Repository\Repository;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Modules\Event\Entities\Event;
use Modules\Event\Entities\Participant;

class ParticipantRepository implements Repository
{
    /** @var Participant $participant */
    private $participant;

    /**
     * ParticipantRepository constructor.
     * @param Participant $participant
     */
    public function __construct(Participant $participant)
    {
        $this->participant = $participant;
    }

    public function all(): Collection
    {
        // TODO: Implement all() method.
    }

    public function create(Builder $data): Model
    {
        return $this->participant->create($data->build());
    }

    public function update(Builder $data, $id): bool
    {
        // TODO: Implement update() method.
    }

    public function delete($id): bool
    {
        return $this->participant->where("id", $id)->delete();
    }

    public function show($id): Model
    {
        return $this->participant->findOrFail($id);
    }

    /**
     * @param $participantId
     * @param Event $event
     * @return EloquentBuilder|Model|object|null
     */
    public function isParticipantRegistered($participantId, Event $event)
    {
        if ($event->isTeamEvent) {
            $lookFor = "page_id";
        } else {
            $lookFor = "user_id";
        }

        return $this->participant
            ->where("event_id", $event->id)
            ->where($lookFor, $participantId)
            ->first();
    }

    public function findByUserId($userId)
    {
        return $this->participant->where("user_id", $userId)->first();
    }

    public function findByPageId($pageId)
    {
        return $this->participant->where("page_id", $pageId)->first();
    }
}
