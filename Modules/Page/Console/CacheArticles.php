<?php


namespace Modules\Page\Console;


use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Modules\Page\Entities\Article;

class CacheArticles extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'articles:cache';

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

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $articles = Article::all();

        return Storage::disk("do_spaces")
            ->put("/cache/articles.json", $articles->toJson(), "public");
    }
}
