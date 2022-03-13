<?php


namespace Modules\Market\Http\Controllers;


use Illuminate\Support\Facades\Storage;

class MarketCacheController
{
    public function getBadges()
    {
        return Storage::disk("do_spaces")->get("/cache/badges.json");
    }
}
