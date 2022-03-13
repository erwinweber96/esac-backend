<?php


namespace Modules\Play\Services;


use Illuminate\Support\Collection;
use Modules\Map\Entities\MapPool;
use Modules\Map\ManiaExchange\Entities\Track;
use Modules\Map\ManiaExchange\Repositories\TMXMappackRepository;
use Modules\Page\Entities\Page;
use Modules\Page\Entities\PageProperty;

/**
 * Class HourlyEventService
 * @package Modules\Play\Services
 */
class HourlyEventService
{
    const PAGE_ID = 132;

    const TIME_ATTACK_QUALIFICATION_GROUP_NAME = "Time Attack Qualification";
    const ONE_VS_ONE_GROUP_NAME                = "1v1 Match";
    const QUARTER_FINALS_GROUP_NAME            = "Quarter-Finals";
    const SEMI_FINALS_GROUP_NAME               = "Semi-Finals";
    const FINAL_GROUP_NAME                     = "Final";

    /** @var TMXMappackRepository $mappackRepository */
    private TMXMappackRepository $mappackRepository;

    /**
     * HourlyEventService constructor.
     * @param TMXMappackRepository $mappackRepository
     */
    public function __construct(TMXMappackRepository $mappackRepository)
    {
        $this->mappackRepository = $mappackRepository;
    }

    /**
     * @return PageProperty
     */
    public function getRandomMapPool()
    {
        /** @var PageProperty[]|Collection $mapPools */
        $mapPools = PageProperty::where("page_id", self::PAGE_ID)
            ->where("key", PageProperty::PLAY_MX_POOL)
            ->get();

        $randomMapPool = $mapPools->random(1);

        return $randomMapPool->first();
    }

    /**
     * @param PageProperty $mapPool
     * @return Track
     */
    public function getRandomMap(PageProperty $mapPool)
    {
        $tracks = $this->mappackRepository->getTracks($mapPool->value);
        $randomIndex = rand(0, count($tracks)-1);
        return $tracks[$randomIndex];
    }
}
