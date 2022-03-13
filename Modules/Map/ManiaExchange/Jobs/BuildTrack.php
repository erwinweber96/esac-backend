<?php


namespace Modules\Map\ManiaExchange\Jobs;

use Modules\Map\ManiaExchange\Entities\Track;

/**
 * Class BuildTrack
 * @package Modules\Map\ManiaExchange\Jobs
 */
class BuildTrack
{
    /**
     * @param array $data
     * @return Track
     */
    public function execute(array $data)
    {
        $track = new Track();

        $track->setId($data['TrackID']);
        $track->setTitlePack($data['TitlePack']);
        $track->setEnvironmentName($data['EnvironmentName']);
        $track->setUsername($data['Username']);
        $track->setTrackUid($data['TrackUID']);
        $track->setName($data['Name']);

        return $track;
    }
}
