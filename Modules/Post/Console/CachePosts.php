<?php


namespace Modules\Post\Console;


use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Event\Entities\Event;
use Modules\Page\Entities\Page;
use Modules\Post\Entities\Post;

class CachePosts extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'posts:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
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

        return Storage::disk("do_spaces")
            ->put("/cache/posts.json", $posts->toJson(), "public");
    }
}
