<?php

namespace Modules\Post\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Event\Entities\Event;
use Modules\Event\Entities\EventModeratorRole;
use Modules\Event\Repositories\EventRepository;
use Modules\Page\Entities\Page;
use Modules\Page\Entities\PageMemberRole;
use Modules\Page\Repositories\PageRepository;
use Modules\Post\Entities\Post;
use Modules\Post\Events\PostUpdated;
use Modules\Post\Http\Requests\CreatePostRequest;
use Modules\Post\Http\Requests\EditPostRequest;
use Modules\Post\Jobs\BuildPost;
use Modules\Post\Repositories\PostRepository;
use Modules\User\Entities\User;

class PostController extends Controller
{
    /** @var BuildPost $buildPost */
    private $buildPost;

    /** @var PostRepository $postRepository */
    private $postRepository;

    /** @var PageRepository $pageRepository */
    private $pageRepository;

    /** @var EventRepository $eventRepository */
    private $eventRepository;

    /**
     * PostController constructor.
     * @param BuildPost $buildPost
     * @param PostRepository $postRepository
     * @param PageRepository $pageRepository
     * @param EventRepository $eventRepository
     */
    public function __construct(BuildPost $buildPost, PostRepository $postRepository, PageRepository $pageRepository, EventRepository $eventRepository)
    {
        $this->buildPost = $buildPost;
        $this->postRepository = $postRepository;
        $this->pageRepository = $pageRepository;
        $this->eventRepository = $eventRepository;
    }

    public function create(CreatePostRequest $request)
    {
        $request->validated();

        /** @var User $user */
        $user = auth()->user();

        if ($pageId = $request->pageId) {
            /** @var Page $page */
            $page = $this->pageRepository->show($pageId);

            if ($user->cannot(PageMemberRole::CREATE_POSTS, [$page])) {
                return response()->json([
                    "errors" => [
                        "message" => ["Not Authorized"]
                    ]
                ], Response::HTTP_UNAUTHORIZED);
            }
        }

        if ($eventId = $request->eventId) {
            /** @var Event $event */
            $event = $this->eventRepository->show($eventId);

            if ($user->cannot(EventModeratorRole::CREATE_POSTS, [$event])) {
                return response()->json([
                    "errors" => [
                        "message" => ["Not Authorized"]
                    ]
                ], Response::HTTP_UNAUTHORIZED);
            }
        }

        $builder = $this->buildPost->execute($request);
        $post    = $this->postRepository->create($builder);

        event(new PostUpdated());

        return response()->json(["post" => $post], Response::HTTP_OK);
    }

    public function getPosts()
    {
        $posts = Post::orderBy("created_at", "desc")->get();

        $posts = $posts->map(function (Post $post) {
           if ($post->pageId) {
               $post->page = DB::table(Page::TABLE_NAME)
                   ->where("id", $post->pageId)
                   ->first(["name", "slug"]);
           }

            if ($post->eventId) {
                $post->event = DB::table(Event::TABLE_NAME)
                    ->where("id", $post->eventId)
                    ->first(["name", "slug"]);
            }

            return $post;
        });

        return $posts;
    }

    public function update(EditPostRequest $request)
    {
        $request->validated();

        /** @var User $user */
        $user = auth()->user();

        if ($pageId = $request->pageId) {
            /** @var Page $page */
            $page = $this->pageRepository->show($pageId);

            if ($user->cannot(PageMemberRole::CREATE_POSTS, [$page])) {
                return response()->json([
                    "errors" => [
                        "message" => ["Not Authorized"]
                    ]
                ], Response::HTTP_UNAUTHORIZED);
            }
        }

        if ($eventId = $request->eventId) {
            /** @var Event $event */
            $event = $this->eventRepository->show($eventId);

            if ($user->cannot(EventModeratorRole::CREATE_POSTS, [$event])) {
                return response()->json([
                    "errors" => [
                        "message" => ["Not Authorized"]
                    ]
                ], Response::HTTP_UNAUTHORIZED);
            }
        }

        Post::where("id", $request->id)->update([
            "content" => $request->content
        ]);

        event(new PostUpdated());

        return response()->json([
            "post" => Post::where("id", $request->id)->first()
        ], Response::HTTP_OK);
    }

    /**
     * @param $postId
     * @return bool|\Illuminate\Http\JsonResponse|null
     * @throws \Exception
     */
    public function delete($postId)
    {
        /** @var User $user */
        $user = auth()->user();

        /** @var Post $post */
        $post = Post::where("id", $postId)->first();

        if ($post->pageId) {
            if ($user->cannot(PageMemberRole::CREATE_POSTS, [$post->page])) {
                return response()->json([
                    "errors" => [
                        "message" => ["Not Authorized"]
                    ]
                ], Response::HTTP_UNAUTHORIZED);
            }
        }

        if ($post->eventId) {
            if ($user->cannot(EventModeratorRole::CREATE_POSTS, [$post->event])) {
                return response()->json([
                    "errors" => [
                        "message" => ["Not Authorized"]
                    ]
                ], Response::HTTP_UNAUTHORIZED);
            }
        }

        $deleted = $post->delete();
        event(new PostUpdated());

        return $deleted;
    }

    public function get($postId) {
        return Post::where("id", $postId)
            ->with(["page", "event"])
            ->first();
    }
}
