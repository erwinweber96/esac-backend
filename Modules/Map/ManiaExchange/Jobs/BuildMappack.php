<?php


namespace Modules\Map\ManiaExchange\Jobs;


use Modules\Map\ManiaExchange\Entities\Mappack;

/**
 * Class BuildMappack
 * @package Modules\Map\ManiaExchange\Jobs
 */
class BuildMappack
{
    /**
     * @param array $data
     * @return Mappack
     */
    public function execute(array $data)
    {
        $mappack = new Mappack();

        if (isset($data['TitlePack'])) {
            $titlePack = $data['TitlePack'];
        } else if (isset($data['Titlepack'])) {
            $titlePack = $data['Titlepack'];
        } else {
            $titlePack = null;
        }

        $mappack->setId($data['ID']);
        $mappack->setUsername($data['Username']);
        $mappack->setEnvironmentName($data['EnvironmentName']);
        $mappack->setStyleName($data['StyleName']);
        $mappack->setTitlePack($titlePack);
        $mappack->setName($data['Name']);

        return $mappack;
    }
}
