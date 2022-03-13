<?php


namespace Modules\Event\Builders;


use App\Model\Builder;
use Illuminate\Support\Str;

class EventBuilder implements Builder
{
    /** @var array */
    private $eventData;

    public function prepare(): Builder
    {
        $this->eventData = [];
        return $this;
    }

    public function setIsTeamEvent(bool $isTeamEvent): self
    {
        $this->eventData['is_team_event'] = $isTeamEvent;
        return $this;
    }

    public function setName(string $name, bool $updateSlug = true): self
    {
        $this->eventData['name'] = $name;
        if ($updateSlug) {
            $this->eventData['slug'] = Str::slug($name);
        }
        return $this;
    }

    public function setSlug(string $name): self
    {
        $this->eventData['slug'] = Str::slug($name);
        return $this;
    }

    public function setAbout(string $about): self
    {
        $this->eventData['about'] = $about;
        return $this;
    }

    public function setType(string $type): self
    {
        $this->eventData['type'] = $type;
        return $this;
    }

    public function setPageId(int $pageId): self
    {
        $this->eventData['page_id'] = $pageId;
        return $this;
    }

    public function setGameId(int $gameId): self
    {
        $this->eventData['game_id'] = $gameId;
        return $this;
    }

    public function setStatusId(int $statusId): self
    {
        $this->eventData['status_id'] = $statusId;
        return $this;
    }

    public function setPrivate(bool $private): self
    {
        $this->eventData['private'] = $private;
        return $this;
    }

    public function setRegistrationOpen(bool $registrationOpen): self
    {
        $this->eventData['registration_open'] = $registrationOpen;
        return $this;
    }

    public function setRequiredGameAccount(bool $requiredGameAccount): self
    {
        $this->eventData['required_game_account'] = $requiredGameAccount;
        return $this;
    }

    public function build()
    {
        return $this->eventData;
    }
}
