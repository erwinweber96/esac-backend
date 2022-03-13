<?php


namespace Modules\Post\Builders;


use App\Model\Builder;

class PostBuilder implements Builder
{
    /** @var array $post */
    private $post;

    public function prepare(): Builder
    {
        $this->post = [];
        return $this;
    }

    public function build()
    {
        return $this->post;
    }

    public function setContent(string $content): self
    {
        $this->post['content'] = $content;
        return $this;
    }

    public function setUserId(int $userId): self
    {
        $this->post['user_id'] = $userId;
        return $this;
    }

    public function setPageId(int $pageId): self
    {
        $this->post['page_id'] = $pageId;
        return $this;
    }

    public function setEventId(int $eventId): self
    {
        $this->post['event_id'] = $eventId;
        return $this;
    }
}
