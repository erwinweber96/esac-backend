<?php


namespace Modules\Map\ManiaExchange\Repositories;


use GuzzleHttp\Client;
use Modules\Map\ManiaExchange\Entities\Mappack;
use Modules\Map\ManiaExchange\Entities\Track;
use Modules\Map\ManiaExchange\Jobs\BuildMappack;
use Modules\Map\ManiaExchange\Jobs\BuildTrack;

class TMXMappackRepository implements MappackRepositoryInterface
{
    //https://{site}/api/mappack/get_info/{id}?={secret}
    const MAPPACK_INFO = "https://trackmania.exchange/api/mappack/get_info/";

    //https://{site}/api/mappack/get_mappack_tracks/{ID}?={secret}
    const MAPPACK_TRACKS = "https://trackmania.exchange/api/mappack/get_mappack_tracks/";

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
        $response = $this->client->get(self::MAPPACK_INFO . $id);
        $json     = $response->getBody()->getContents();
        $data     = json_decode($json, true);

        if (!$data) {
            return null;
        }

        if (isset($data['Message'])) {
            if ($data['Message'] == "Mappack does not exist.") {
                return null;
            }
        }

        return $this->buildMappack->execute($data);
    }

    /**
     * @param int $id
     * @return Track[]
     */
    public function getTracks(int $id): array
    {
        $response = $this->client->get(self::MAPPACK_TRACKS . $id);
        $json     = $response->getBody()->getContents();
        $data     = json_decode($json, true);

        $tracks = [];
        foreach ($data as $track) {
            $tracks[] = $this->buildTrack->execute($track);
        }

        return $tracks;
    }
}
