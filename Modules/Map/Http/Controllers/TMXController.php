<?php


namespace Modules\Map\Http\Controllers;


use Modules\Map\ManiaExchange\Repositories\TMXMappackRepository;

class TMXController
{
    /** @var TMXMappackRepository $mappackRepository */
    private $mappackRepository;

    /**
     * TMXController constructor.
     * @param TMXMappackRepository $mappackRepository
     */
    public function __construct(TMXMappackRepository $mappackRepository)
    {
        $this->mappackRepository = $mappackRepository;
    }

    public function getTracks($mappackId)
    {
        return $this->mappackRepository->getTracks($mappackId);
    }
}
