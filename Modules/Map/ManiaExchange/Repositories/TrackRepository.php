<?php


namespace Modules\Map\ManiaExchange\Repositories;


use GuzzleHttp\Client;
use Modules\Map\ManiaExchange\Entities\Track;
use Modules\Map\ManiaExchange\Jobs\BuildTrack;

/**
 * Class TrackRepository
 * @package Modules\Map\ManiaExchange\Repositories
 */
class TrackRepository implements TrackRepositoryInterface
{
    const BASE_URL = "https://api.mania-exchange.com/tm/maps/";

    /** @var Track $track */
    private $track;

    /** @var Client $client */
    private $client;

    /** @var BuildTrack $buildTrack */
    private $buildTrack;

    /**
     * TrackRepository constructor.
     * @param Track $track
     * @param Client $client
     * @param BuildTrack $buildTrack
     */
    public function __construct(Track $track, Client $client, BuildTrack $buildTrack)
    {
        $this->track = $track;
        $this->client = $client;
        $this->buildTrack = $buildTrack;
    }

    public function findById(int $id)
    {
        $response = $this->client->get(self::BASE_URL . $id);
        $json     = $response->getBody()->getContents();
        $data     = json_decode($json, true);

        if (!$data) {
            return null;
        }

        return $this->buildTrack->execute($data[0]);
    }
}
