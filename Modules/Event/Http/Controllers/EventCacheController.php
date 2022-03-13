<?php


namespace Modules\Event\Http\Controllers;


use Illuminate\Support\Facades\Storage;


/**
 * Class EventCacheController
 * @package Modules\Event\Http\Controllers
 */
class EventCacheController
{
    public function getEvents()
    {
        return Storage::disk("do_spaces")->get("/cache/events.json");
    }

    public function getFeaturedEvents()
    {
        return Storage::disk("do_spaces")->get("/cache/featured_events.json");
    }
}
