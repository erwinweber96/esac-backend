<?php


namespace Modules\Page\Http\Controllers;


use Illuminate\Support\Facades\Storage;

class ArticleCacheController
{
    public function getArticles()
    {
        return Storage::disk("do_spaces")->get("/cache/articles.json");
    }
}
