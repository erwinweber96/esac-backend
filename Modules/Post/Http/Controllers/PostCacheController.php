<?php


namespace Modules\Post\Http\Controllers;


use Illuminate\Support\Facades\Storage;

class PostCacheController
{
    public function getPosts()
    {
        return Storage::disk("do_spaces")->get("/cache/posts.json");
    }
}
