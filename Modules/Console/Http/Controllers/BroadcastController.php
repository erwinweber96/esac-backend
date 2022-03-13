<?php


namespace Modules\Console\Http\Controllers;


use Illuminate\Http\Request;
use Modules\Console\Events\GenericEvent;
use Modules\Console\Entities\GenericEventData;

/**
 * Class BroadcastController
 * @package Modules\Console\Http\Controllers
 */
class BroadcastController
{
    public function genericEvent(Request $request)
    {
        $eventData = new GenericEventData();

        $eventData->setChannel($request->input("channel"));
        $eventData->setData($request->input("data"));
        $eventData->setName($request->input("name"));

        event(new GenericEvent($eventData));
    }
}
