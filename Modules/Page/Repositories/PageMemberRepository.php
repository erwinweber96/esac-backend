<?php


namespace Modules\Page\Repositories;


use App\Model\Builder;
use App\Repository\Repository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Modules\Page\Entities\PageMember;

class PageMemberRepository implements Repository
{
    /** @var PageMember $pageMember */
    private $pageMember;

    /**
     * PageMemberRepository constructor.
     * @param PageMember $pageMember
     */
    public function __construct(PageMember $pageMember)
    {
        $this->pageMember = $pageMember;
    }

    public function all(): Collection
    {
        // TODO: Implement all() method.
    }

    public function create(Builder $data): Model
    {
        return $this->pageMember->create($data->build());
    }

    public function update(Builder $data, $id): bool
    {
        // TODO: Implement update() method.
    }

    public function delete($id): bool
    {
        // TODO: Implement delete() method.
    }

    public function show($id): Model
    {
        // TODO: Implement show() method.
    }
}
