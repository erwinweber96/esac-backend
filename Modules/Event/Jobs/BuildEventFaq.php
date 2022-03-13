<?php


namespace Modules\Event\Jobs;


use Modules\Event\Builders\EventFaqBuilder;
use Modules\Event\Http\Requests\CreateEventFaqRequest;

class BuildEventFaq
{
    /** @var EventFaqBuilder $builder */
    private $builder;

    /**
     * BuildEventFaq constructor.
     * @param EventFaqBuilder $builder
     */
    public function __construct(EventFaqBuilder $builder)
    {
        $this->builder = $builder->prepare();
    }

    public function execute(CreateEventFaqRequest $request)
    {
        return $this->builder
            ->setEventId($request->eventId)
            ->setAnswer($request->answer)
            ->setQuestion($request->question);
    }
}
