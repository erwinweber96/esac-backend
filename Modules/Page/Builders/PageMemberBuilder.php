<?php


namespace Modules\Page\Builders;


use App\Model\Builder;

class PageMemberBuilder implements Builder
{
    /** @var array $pageMember */
    private $pageMember;

    public function prepare(): Builder
    {
        $this->pageMember = [];
        return $this;
    }

    public function build()
    {
        return $this->pageMember;
    }

    public function setUserId($userId): self
    {
        $this->pageMember['user_id'] = $userId;
        return $this;
    }

    public function setPageId($pageId): self
    {
        $this->pageMember['page_id'] = $pageId;
        return $this;
    }

    public function setTypeId($typeId): self
    {
        $this->pageMember['type_id'] = $typeId;
        return $this;
    }
}
