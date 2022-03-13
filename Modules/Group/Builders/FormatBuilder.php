<?php


namespace Modules\Group\Builders;


use App\Model\Builder;

class FormatBuilder implements Builder
{
    /** @var array $format */
    private $format;

    public function prepare(): Builder
    {
        $this->format = [];
        return $this;
    }

    public function setName(string $name): self
    {
        $this->format['name'] = $name;
        return $this;
    }

    public function setInheritable(bool $inheritable): self
    {
        $this->format['inheritable'] = $inheritable;
        return $this;
    }

    public function setAreResultsAdditive(bool $areResultsAdditive): self
    {
        $this->format['are_results_additive'] = $areResultsAdditive;
        return $this;
    }

    public function setIsGameServerGuarded(bool $isGameServerGuarded): self
    {
        $this->format['is_game_server_guarded'] = $isGameServerGuarded;
        return $this;
    }

    public function setMatchModifiableByParticipants(bool $matchModifiableByParticipants): self
    {
        $this->format['match_modifiable_by_participants'] = $matchModifiableByParticipants;
        return $this;
    }

    public function setRequiresModeratorApproval(bool $requiresModeratorApproval): self
    {
        $this->format['requires_moderator_approval'] = $requiresModeratorApproval;
        return $this;
    }

    public function setTypeId(int $type): self
    {
        $this->format['type_id'] = $type;
        return $this;
    }

    public function setEventId(int $eventId): self
    {
        $this->format['event_id'] = $eventId;
        return $this;
    }

    public function setDescription(string $description)
    {
        $this->format['description'] = $description;
        return $this;
    }

    public function build()
    {
        return $this->format;
    }
}
