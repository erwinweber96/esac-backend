<?php


namespace Modules\Group\Jobs;


use Modules\Group\Builders\FormatBuilder;
use Modules\Group\Http\Requests\UpdateFormatNameRequest;

class BuildFormatForNameUpdate
{
    /** @var FormatBuilder $builder */
    private $builder;

    /**
     * BuildFormatForNameUpdate constructor.
     * @param FormatBuilder $builder
     */
    public function __construct(FormatBuilder $builder)
    {
        $this->builder = $builder->prepare();
    }

    public function execute(UpdateFormatNameRequest $request)
    {
        return $this->builder->setName($request->formatName);
    }
}
