<?php


namespace Modules\Console\Api;


use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Response;
use Modules\Match\Entities\MatchModel;

/**
 * Class DedicatedControllerApi
 * @package Modules\Console\Api
 *
 * Sends data to the JS Dedicated Controller.
 */
class DedicatedControllerApi
{
    //TODO: replace with env variable
    const DEDICATED_CONTROLLER_HOST_IP = "xxx.xxx.xxx.xxx";

    /** @var Client $client */
    private $client;

    /**
     * DedicatedControllerApi constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param $port
     * @param array $maps
     * @param array $matchSettings
     * @param array $format
     * @param array $participants
     * @param int $matchId
     * @param array $matchEndCondition
     * @param array $teams
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function startMatch(
        $port,
        array $maps,
        array $matchSettings,
        array $format,
        array $participants,
        int $matchId,
        array $matchEndCondition,
        array $teams = []
    ) {
        $params = [
            "maps"              => $maps,
            "matchSettings"     => $matchSettings,
            "format"            => $format,
            "participants"      => $participants,
            "matchId"           => $matchId,
            "matchEndCondition" => $matchEndCondition,
            "token"             => 'xxxxxxxxxxxxxxxxxxxxxxx' //TODO: change this
        ];

        if ($teams) {
            $params['teams'] = $teams;
        }

        return $this->client->post(self::DEDICATED_CONTROLLER_HOST_IP.":$port/match", [
            RequestOptions::JSON => $params
        ]);
    }

    public function updateWhitelist($port, $whitelist) {
        $params = [
            "participants"      => $whitelist,
            "token"             => 'xxxxxxxxxxxxxxxxxxxxxxx' //TODO: change this
        ];

        return $this->client->put(self::DEDICATED_CONTROLLER_HOST_IP.":$port/whitelist", [
            RequestOptions::JSON => $params
        ]);
    }

    /**
     * @param $port
     * @return bool
     */
    public function isServerUp($port)
    {
        try {
            $response = $this->client->get(self::DEDICATED_CONTROLLER_HOST_IP . ":$port/ping");
        } catch (\Throwable $exception) {
            return false;
        }

         if ($response->getStatusCode() == Response::HTTP_OK) {
             return true;
         }

         return false;
    }

    public function cancelMatch($port)
    {
        $response = $this->client->post(self::DEDICATED_CONTROLLER_HOST_IP . ":$port/cancel");
    }
}
