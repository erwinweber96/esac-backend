<?php


namespace Modules\Match\Jobs;


use Modules\Match\Builders\MatchBuilder;
use Modules\Match\Http\Requests\UpdateMatchNameRequest;

class BuildMatchForNameUpdate
{
    /** @var MatchBuilder */
    private $builder;

    /**
     * BuildMatchForNameUpdate constructor.
     * @param MatchBuilder $builder
     */
    public function __construct(MatchBuilder $builder)
    {
        $this->builder = $builder->prepare();
    }

    public function execute(UpdateMatchNameRequest $request)
    {
        return $this->builder->setName($request->matchName);
    }
}
