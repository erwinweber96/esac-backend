<?php


namespace Modules\Post\Jobs;


use Modules\Post\Builders\PostBuilder;
use Modules\Post\Http\Requests\CreatePostRequest;

class BuildPost
{
    /** @var PostBuilder $postBuilder */
    private $postBuilder;

    /**
     * CreatePost constructor.
     * @param PostBuilder $postBuilder
     */
    public function __construct(PostBuilder $postBuilder)
    {
        $this->postBuilder = $postBuilder->prepare();
    }

    public function execute(CreatePostRequest $request)
    {
        $builder = $this->postBuilder->setContent($request->content);

        if ($userId = $request->userId) {
            $builder = $builder->setUserId($userId);
        }

        if ($eventId = $request->eventId) {
            $builder = $builder->setEventId($eventId);
        }

        if ($pageId = $request->pageId) {
            $builder = $builder->setPageId($pageId);
        }

        return $builder;
    }
}
