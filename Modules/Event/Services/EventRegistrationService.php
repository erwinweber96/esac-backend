<?php


namespace Modules\Event\Services;


use Modules\Event\Entities\Event;
use Modules\Event\Entities\EventProperty;
use Modules\Event\Exceptions\DiscordNotSet;
use Modules\Event\Exceptions\GameAccountNotLinked;
use Modules\Event\Exceptions\NonTwitchFollower;
use Modules\Event\Exceptions\NonTwitchSubscriber;
use Modules\Event\Exceptions\NoSlotAvailable;
use Modules\Event\Exceptions\ParticipantAlreadyRegistered;
use Modules\Event\Exceptions\RegistrationClosed;
use Modules\Event\Exceptions\TwitchNotSet;
use Modules\Event\Repositories\ParticipantRepository;
use Modules\Event\Validators\GameAccountValidator;
use Modules\Twitch\Entities\TwitchAccessToken;
use Modules\Twitch\Services\TwitchService;
use Modules\User\Entities\User;

/**
 * Class EventRegistrationService
 * @package Modules\Event\Services
 */
class EventRegistrationService
{

    /** @var ParticipantRepository $participantRepository */
    private $participantRepository;

    /**
     * EventRegistrationService constructor.
     * @param ParticipantRepository $participantRepository
     */
    public function __construct(ParticipantRepository $participantRepository)
    {
        $this->participantRepository = $participantRepository;
    }


    /**
     * @param Event $event
     * @param $participantId
     *
     * @throws DiscordNotSet
     * @throws GameAccountNotLinked
     * @throws NoSlotAvailable
     * @throws NonTwitchFollower
     * @throws ParticipantAlreadyRegistered
     * @throws RegistrationClosed
     * @throws TwitchNotSet
     */
    public function validateRegistrationRequirements(Event $event, $participantId)
    {
        if (!$event->registrationOpen) {
            throw new RegistrationClosed();
        }

        $participant = $this->participantRepository
            ->isParticipantRegistered($participantId, $event);

        if ($participant) {
            throw new ParticipantAlreadyRegistered();
        }

        $gameAccountValidator = new GameAccountValidator($event);
        $gameAccountValidator->validate();

        /** @var EventProperty $limit */
        $limit = $event->properties->where("key", EventProperty::PARTICIPANTS_LIMIT)->first();
        if ($limit) {
            if ($event->participants->count() >= $limit->value) {
                throw new NoSlotAvailable();
            }
        }

        /** @var EventProperty $discordRequired */
        $discordRequired = $event->properties->where("key", EventProperty::DISCORD_REQUIRED)->first();
        if ($discordRequired) {
            if (!$event->isTeamEvent && $discordRequired->value != 0) {
                /** @var User $user */
                $user = auth()->user();

                if (!$user->discord) {
                    throw new DiscordNotSet();
                }
            }
        }

        /** @var TwitchAccessToken $twitchFollowerOnly */
        $twitchFollowerOnly = $event
            ->properties
            ->where("key", EventProperty::TWITCH_FOLLOWER_ONLY)
            ->first();

        if ($twitchFollowerOnly) {
            /** @var TwitchService $twitchService */
            $twitchService = app(TwitchService::class);

            $userId = auth()->user()->id;

            if ($userId != $event->page->user->id) {
                if (!$twitchService->getAccessTokenByUserId($userId)) {
                    throw new TwitchNotSet();
                }

                if (!$twitchService->isUserFollowingEventOwner($userId, $event->id)) {
                    throw new NonTwitchFollower();
                }
            }
        }

        /** @var TwitchAccessToken $twitchSubscriberOnly */
        $twitchSubscriberOnly = $event
            ->properties
            ->where("key", EventProperty::TWITCH_SUBSCRIBER_ONLY)
            ->first();

        if ($twitchSubscriberOnly) {
            /** @var TwitchService $twitchService */
            $twitchService = app(TwitchService::class);

            $userId = auth()->user()->id;

            if ($userId != $event->page->user->id) {
                if (!$twitchService->getAccessTokenByUserId($userId)) {
                    throw new TwitchNotSet();
                }

                if (!$twitchService->isUserSubscribedToEventOwner($userId, $event->id)) {
                    throw new NonTwitchSubscriber();
                }
            }
        }
    }
}
