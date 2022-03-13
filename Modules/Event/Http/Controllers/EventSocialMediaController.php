<?php


namespace Modules\Event\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Event\Entities\Event;
use Modules\Link\Entities\Link;
use Modules\User\Entities\User;

class EventSocialMediaController
{
    public function create(Request $request)
    {
        /** @var User $user */
        $user = auth()->user();

        /** @var Event $event */
        $event = Event::where("id", $request->input("eventId"))->first();

        if ($user->id != $event->page->user->id) {
            return response()->json([
                "errors" => [
                    "message" => ["Not Authorized"]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        foreach ($request->input("links") as $socialMedia) {
            if (!$socialMedia['url']) {
                continue;
            }

            $existing = Link::where("event_id", $request->input("eventId"))
                ->where("name", $socialMedia["platform"]);

            if ($existing->count()) {
                $existing->update([
                    "url" => $socialMedia["url"]
                ]);
                continue;
            }

            $link = new Link();

            $link->name    = $socialMedia['platform'];
            $link->url     = $socialMedia['url'];
            $link->eventId = $request->input("eventId");
            $link->pending = false;

            if (isset($socialMedia['typeId'])) {
                $link->typeId = $socialMedia['typeId'];
            }

            $link->save();
        }

        return response()->json(["message" => "Success."], Response::HTTP_OK);
    }

    public function delete($linkId)
    {
        /** @var User $user */
        $user = auth()->user();

        /** @var Link $link */
        $link = Link::where("id", $linkId)->first();

        if ($user->id != $link->event->page->user->id) {
            return response()->json([
                "errors" => [
                    "message" => ["Not Authorized"]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $link->delete();
        } catch (\Throwable $exception) {
            return response()->json([
                "errors" => [
                    "message" => ["Could not delete."]
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json(["message" => "Success."], Response::HTTP_OK);
    }
}
