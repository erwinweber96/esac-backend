<?php


namespace Modules\Twitch\Services;


use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Modules\Event\Entities\Event;
use Modules\Event\Exceptions\TwitchNotSet;
use Modules\Page\Entities\Page;
use Modules\Twitch\Entities\TwitchAccessToken;
use Modules\User\Entities\User;

/**
 * Class TwitchService
 * @package Modules\Twitch\Services
 */
class TwitchService
{
    /** @var Client $client */
    private $client;

    /**
     * TwitchService constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param $userId
     * @return string
     */
    public function getAccessTokenByUserId($userId)
    {
        /** @var TwitchAccessToken $twitchAccessToken */
        $twitchAccessToken = TwitchAccessToken::where("user_id", $userId)->first();
        return $twitchAccessToken->accessToken;
    }

    /**
     * @param $code
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getAuthByCode($code)
    {
        $response = $this->client->request(
            "POST",
            "https://id.twitch.tv/oauth2/token",
            [
                'form_params' => [
                    'code' => $code,
                    'client_id' => env("TWITCH_HELIX_KEY"),
                    'client_secret' => env("TWITCH_HELIX_SECRET"),
                    'redirect_uri' => "https://esac.gg/twitch/redirect",
                    'grant_type' => 'authorization_code'
                ]
            ]
        );

        return json_decode($response->getBody()->getContents(), true);
    }

    public function refreshToken($userId)
    {
        /** @var TwitchAccessToken $twitchAccess */
        $twitchAccess = TwitchAccessToken::where("user_id", $userId)->first();

        $response = $this->client->request(
            "POST",
            "https://id.twitch.tv/oauth2/token",
            [
                'form_params' => [
                    'refresh_token' => $twitchAccess->refreshToken,
                    'client_id' => env("TWITCH_HELIX_KEY"),
                    'client_secret' => env("TWITCH_HELIX_SECRET"),
                    'redirect_uri' => "https://esac.gg/twitch/redirect",
                    'grant_type' => 'refresh_token'
                ]
            ]
        );

        $response = json_decode($response->getBody()->getContents(), true);

        $twitchAccess->accessToken = $response['access_token'];
        $twitchAccess->refreshToken = $response['refresh_token'];
        $twitchAccess->save();

        return $twitchAccess;
    }

    /**
     * @param $userId
     * @return mixed
     */
    public function getTwitchUser($userId)
    {
        $token = $this->getAccessTokenByUserId($userId);

        try {
            $response = $this->client->request(
                'GET',
                "https://api.twitch.tv/helix/users",
                [
                    'headers' =>
                        [
                            'Authorization' => "Bearer {$token}",
                            'Client-Id' => env("TWITCH_HELIX_KEY")
                        ]
                ]
            );
        } catch (\Throwable $exception) {
            $twitchAccess = $this->refreshToken($userId);

            $response = $this->client->request(
                'GET',
                "https://api.twitch.tv/helix/users",
                [
                    'headers' =>
                        [
                            'Authorization' => "Bearer {$twitchAccess->accessToken}",
                            'Client-Id' => env("TWITCH_HELIX_KEY")
                        ]
                ]
            );
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @param $userId
     * @return mixed
     */
    public function getStreamInfo($userId)
    {
        $token = $this->getAccessTokenByUserId($userId);

        $user = $this->getTwitchUser($userId);
        $login = $user['data'][0]['login'];

        try {
            $response = $this->client->request(
                'GET',
                "https://api.twitch.tv/helix/streams?user_login=$login",
                [
                    'headers' =>
                        [
                            'Authorization' => "Bearer {$token}",
                            'Client-Id' => env("TWITCH_HELIX_KEY")
                        ]
                ]
            );
        } catch (\Throwable $exception) {
            $twitchAccess = $this->refreshToken($userId);

            $response = $this->client->request(
                'GET',
                "https://api.twitch.tv/helix/streams?user_login=$login",
                [
                    'headers' =>
                        [
                            'Authorization' => "Bearer {$twitchAccess->accessToken}",
                            'Client-Id' => env("TWITCH_HELIX_KEY")
                        ]
                ]
            );
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @param $userId
     * @param int $cursor
     * @return mixed
     *
     * DON'T USE IT UNLESS YOU ARE AWARE OF THE RATE LIMITATION AND STILL THINK IT'S FINE FOR YOUR USE CASE
     */
    public function getFollowers($userId, $cursor = "")
    {
        $userData = $this->getTwitchUser($userId);
        $token    = $this->getAccessTokenByUserId($userId);

        $user         = $userData['data'][0];
        $twitchUserId = $user['id'];

        $url = "https://api.twitch.tv/helix/users/follows?from_id={$twitchUserId}";
        if ($cursor) {
            $url .= "&after={$cursor}";
        }

        try {
            $response = $this->client->request(
                'GET',
                $url,
                [
                    'headers' =>
                        [
                            'Authorization' => "Bearer {$token}",
                            'Client-Id' => env("TWITCH_HELIX_KEY")
                        ]
                ]
            );
        } catch (\Throwable $exception) {
            $twitchAccess = $this->refreshToken($userId);

            $response = $this->client->request(
                'GET',
                $url,
                [
                    'headers' =>
                        [
                            'Authorization' => "Bearer {$twitchAccess->accessToken}",
                            'Client-Id' => env("TWITCH_HELIX_KEY")
                        ]
                ]
            );
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    public function isUserFollowingEventOwner($userId, $eventId)
    {
        $event = DB::table(Event::TABLE_NAME)
            ->where("id", $eventId)
            ->first(['page_id']);

        $page = DB::table(Page::TABLE_NAME)
            ->where("id", $event->page_id)
            ->first(['user_id']);

        $owner          = $this->getTwitchUser($page->user_id);
        $owner          = $owner['data'][0];
        $ownerTwitchId  = $owner['id'];

        $userData = $this->getTwitchUser($userId);
        $token    = $this->getAccessTokenByUserId($userId);

        $user         = $userData['data'][0];
        $twitchUserId = $user['id'];

        $url = "https://api.twitch.tv/helix/users/follows?from_id={$twitchUserId}&to_id=${ownerTwitchId}";

        try {
            $response = $this->client->request(
                'GET',
                $url,
                [
                    'headers' =>
                        [
                            'Authorization' => "Bearer {$token}",
                            'Client-Id' => env("TWITCH_HELIX_KEY")
                        ]
                ]
            );
        } catch (\Throwable $exception) {
            $twitchAccess = $this->refreshToken($userId);

            $response = $this->client->request(
                'GET',
                $url,
                [
                    'headers' =>
                        [
                            'Authorization' => "Bearer {$twitchAccess->accessToken}",
                            'Client-Id' => env("TWITCH_HELIX_KEY")
                        ]
                ]
            );
        }

        $data = json_decode($response->getBody()->getContents(), true);
        return !!$data['data'];
    }

    /**
     * @param $userId
     * @param $eventId
     *
     * @return bool
     */
    public function isUserSubscribedToEventOwner($userId, $eventId)
    {
        $event = DB::table(Event::TABLE_NAME)
            ->where("id", $eventId)
            ->first(['page_id']);

        $page = DB::table(Page::TABLE_NAME)
            ->where("id", $event->page_id)
            ->first(['user_id']);

        $owner          = $this->getTwitchUser($page->user_id);
        $owner          = $owner['data'][0];
        $ownerTwitchId  = $owner['id'];

        $userData = $this->getTwitchUser($userId);
        $token    = $this->getAccessTokenByUserId($userId);

        $user         = $userData['data'][0];
        $twitchUserId = $user['id'];

        $url = "https://api.twitch.tv/helix/subscriptions/user?user_id={$twitchUserId}&broadcaster_id=${ownerTwitchId}";

        try {
            $response = $this->client->request(
                'GET',
                $url,
                [
                    'headers' =>
                        [
                            'Authorization' => "Bearer {$token}",
                            'Client-Id' => env("TWITCH_HELIX_KEY")
                        ]
                ]
            );
        } catch (\Throwable $exception) {
            $twitchAccess = $this->refreshToken($userId);

            try {
                $response = $this->client->request(
                    'GET',
                    $url,
                    [
                        'headers' =>
                            [
                                'Authorization' => "Bearer {$twitchAccess->accessToken}",
                                'Client-Id' => env("TWITCH_HELIX_KEY")
                            ]
                    ]
                );
            } catch (\Throwable $exception) {
                return false;
            }
        }

        return true;
    }
}
