<?php


namespace Modules\Page\Repositories;


use App\Model\Builder;
use App\Repository\Repository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Event\Entities\Event;
use Modules\Page\Entities\Page;
use Modules\Page\Entities\PageMemberRole;
use Modules\User\Entities\User;

/**
 * Class PageRepository
 * @package Modules\Page\Repositories
 */
class PageRepository implements Repository
{
    /** @var Page $page */
    private $page;

    /**
     * PageRepository constructor.
     * @param Page $page
     */
    public function __construct(Page $page)
    {
        $this->page = $page;
    }

    public function all(): Collection
    {
        $this->page->with('type')->get();
    }

    public function create(Builder $data): Model
    {
        return $this->page->create($data->build());
    }

    public function update(Builder $data, $id): bool
    {
        $page = $this->show($id);
        return $page->update($data->build());
    }

    public function delete($id): bool
    {
        $page = $this->show($id);
        return $page->delete();
    }

    public function show($id): Model
    {
        return $this->page->where("id", $id)->first();
    }

    public function findBySlug($slug)
    {
        /** @var User $user */
        $user = Auth::guard('api')->user();

        $hidePrivateEvents = true;

        /** @var Page $page */
        $page = $this->page->where("slug", $slug)->first();

        if ($user) {
            if ($user->can(PageMemberRole::SEE_PRIVATE_EVENTS, [$page])) {
                $hidePrivateEvents = false;
            }
        }

        $builder = $this->page->where('slug', $slug)->with([
            'links',
            'posts',
            'properties',
            'type',
            'user',
            'members',
            'members.user',
            'members.roles',
            'participants',
            'participants.groupResults',
            'participants.groupResults.group',
        ]);

        $events = DB::table(Event::TABLE_NAME)
            ->where("page_id", $page->id);

        if ($hidePrivateEvents) {
            $events->where("private", false);
        }

        /** @var Page $page */
        $page = $builder->first();
        $page->events = $events->get();

        return $page;
    }
}
