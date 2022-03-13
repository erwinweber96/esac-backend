<?php


namespace Modules\Event\Validators;


use Modules\Event\Entities\Event;
use Modules\Event\Exceptions\GameAccountNotLinked;
use Modules\Game\Entities\Game;
use Modules\User\Entities\User;

/**
 * Class GameAccountValidator
 * @package Modules\Event\Validators
 */
class GameAccountValidator
{
    /** @var Event $event */
    private $event;

    /**
     * GameAccountValidator constructor.
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    /**
     * @return bool
     * @throws GameAccountNotLinked
     */
    public function validate(): bool
    {
        if (!$this->shouldValidate()) {
            return true;
        }

        /** @var User $user */
        $user = auth()->user();

        switch($this->event->game->name) {
            case Game::TRACKMANIA_2_STADIUM:
                $this->checkManiaplanetAccount($user);
                break;
            case Game::TRACKMANIA:
                $this->checkTrackmaniaNickname($user);
                break;
        }

        return true;
    }

    private function shouldValidate(): bool
    {
        return $this->event->requiredGameAccount && $this->event->isTeamEvent == false;
    }

    /**
     * @param User $user
     * @return bool
     * @throws GameAccountNotLinked
     */
    private function checkManiaplanetAccount(User $user): bool
    {
        if (!$user->maniaplanet) {
            throw new GameAccountNotLinked("Please link your game account from settings before registering.");
        }

        return true;
    }

    /**
     * @param User $user
     * @return bool
     * @throws GameAccountNotLinked
     */
    private function checkTrackmaniaNickname(User $user): bool
    {
        if (!$user->tmNickname) {
            throw new GameAccountNotLinked("Please add your in-game nickname in the settings before registering.");
        }

        return true;
    }
}
