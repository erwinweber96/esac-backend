<?php


namespace Modules\Map\ManiaExchange\Repositories;


use GuzzleHttp\Client;
use Modules\Map\ManiaExchange\Entities\Mappack;
use Modules\Map\ManiaExchange\Entities\Track;
use Modules\Map\ManiaExchange\Jobs\BuildMappack;
use Modules\Map\ManiaExchange\Jobs\BuildTrack;

/**
 * Class MappackRepository
 * @package Modules\Map\ManiaExchange\Repositories
 */
class MappackRepository implements MappackRepositoryInterface
{
    const MAP_PACKS_URL = "https://api.mania-exchange.com/tm/mappacks/";
    const MAP_PACK_URL = "https://api.mania-exchange.com/tm/mappack/";

    /** @var Client $client */
    private $client;

    /** @var Mappack $mappack */
    private $mappack;

    /** @var BuildMappack $buildMappack */
    private $buildMappack;

    /** @var BuildTrack $buildTrack */
    private $buildTrack;

    /**
     * MappackRepository constructor.
     * @param Client $client
     * @param Mappack $mappack
     * @param BuildMappack $buildMappack
     * @param BuildTrack $buildTrack
     */
    public function __construct(Client $client, Mappack $mappack, BuildMappack $buildMappack, BuildTrack $buildTrack)
    {
        $this->client = $client;
        $this->mappack = $mappack;
        $this->buildMappack = $buildMappack;
        $this->buildTrack = $buildTrack;
    }

    /**
     * @param int $id
     * @return Mappack
     */
    public function findById(int $id)
    {
        $response = $this->client->get(self::MAP_PACKS_URL . $id);
        $json     = $response->getBody()->getContents();
        $data     = json_decode($json, true);

        if (!$data) {
            return null;
        }

        return $this->buildMappack->execute($data[0]);
    }

    /**
     * @param int $id
     * @return Track[]
     */
    public function getTracks(int $id): array
    {
        $response = $this->client->get(self::MAP_PACK_URL . $id . "/tracks");
        $json     = $response->getBody()->getContents();
        $data     = json_decode($json, true);

        $tracks = [];
        foreach ($data as $track) {
            $tracks[] = $this->buildTrack->execute($track);
        }

        return $tracks;
    }
}
