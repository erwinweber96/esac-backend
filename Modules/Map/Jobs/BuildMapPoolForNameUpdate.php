<?php


namespace Modules\Map\Jobs;


use Modules\Map\Builders\MapPoolBuilder;
use Modules\Map\Http\Requests\UpdateMapPoolNameRequest;

class BuildMapPoolForNameUpdate
{
    /** @var MapPoolBuilder */
    private $builder;

    /**
     * BuildMapPoolForNameUpdate constructor.
     * @param MapPoolBuilder $builder
     */
    public function __construct(MapPoolBuilder $builder)
    {
        $this->builder = $builder->prepare();
    }

    public function execute(UpdateMapPoolNameRequest $request)
    {
        return $this->builder->setName($request->mapPoolName);
    }
}
