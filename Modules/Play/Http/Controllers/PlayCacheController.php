<?php


namespace Modules\Play\Http\Controllers;


use Illuminate\Support\Facades\Storage;

class PlayCacheController
{
    public function getLeaderboard()
    {
        return Storage::disk("do_spaces")->get("/cache/play_leaderboard.json");
    }
}
