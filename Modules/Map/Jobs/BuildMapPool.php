<?php


namespace Modules\Map\Jobs;


use Modules\Map\Builders\MapPoolBuilder;
use Modules\Map\Http\Requests\CreateMapPoolRequest;

class BuildMapPool
{
    /** @var MapPoolBuilder $builder */
    private $builder;

    /**
     * BuildMapPool constructor.
     * @param MapPoolBuilder $builder
     */
    public function __construct(MapPoolBuilder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * @param CreateMapPoolRequest $request
     * @return MapPoolBuilder
     */
    public function execute(CreateMapPoolRequest $request)
    {
        return $this->builder->prepare()
            ->setEventId($request->input("eventId"))
            ->setName($request->input("name"))
            ->setMxId($request->input("mxId"));
    }
}
