<?php


namespace Modules\Event\Repositories;


use App\Model\Builder;
use App\Repository\Repository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Modules\Event\Entities\EventFaq;

class EventFaqRepository implements Repository
{
    /** @var EventFaq $faq */
    private $faq;

    /**
     * EventFaqRepository constructor.
     * @param EventFaq $faq
     */
    public function __construct(EventFaq $faq)
    {
        $this->faq = $faq;
    }

    public function all(): Collection
    {
        // TODO: Implement all() method.
    }

    public function create(Builder $data): Model
    {
        return $this->faq->create($data->build());
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
