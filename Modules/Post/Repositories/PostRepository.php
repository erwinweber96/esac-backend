<?php


namespace Modules\Post\Repositories;


use App\Model\Builder;
use App\Repository\Repository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Modules\Post\Entities\Post;

class PostRepository implements Repository
{
    /** @var Post $post */
    private $post;

    /**
     * PostRepository constructor.
     * @param Post $post
     */
    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    public function all(): Collection
    {
        // TODO: Implement all() method.
    }

    public function create(Builder $data): Model
    {
        return $this->post->create($data->build());
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
