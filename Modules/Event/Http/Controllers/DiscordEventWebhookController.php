<?php

namespace Modules\Event\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Event\Entities\DiscordEventWebhook;
use Modules\Event\Entities\DiscordEventWebhookUpdateType;
use Modules\Event\Entities\Event;
use Modules\Page\Entities\Page;
use Modules\User\Entities\User;

class DiscordEventWebhookController
{
    public function get($slug)
    {
        /** @var User $user */
        $user = auth()->user();

        $event = DB::table(Event::TABLE_NAME)
            ->where("slug", $slug)
            ->first(["page_id", "id"]);

        $page = DB::table(Page::TABLE_NAME)
            ->where("id", $event->page_id)
            ->first(["user_id"]);

        if ($page->user_id != $user->id) {
            return response()->json([
                "errors" => [
                    "messages" => ["Not Authorized."]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        return DiscordEventWebhook::where("event_id", $event->id)
            ->with('discordEventWebhookUpdateTypes')
            ->first();
    }

    public function save(Request $request)
    {
        /** @var User $user */
        $user = auth()->user();

        $eventId = $request->input("eventId");

        $event = DB::table(Event::TABLE_NAME)
            ->where("id", $eventId)
            ->first(["page_id"]);

        $page = DB::table(Page::TABLE_NAME)
            ->where("id", $event->page_id)
            ->first(["user_id"]);

        if ($page->user_id != $user->id) {
            return response()->json([
                "errors" => [
                    "messages" => ["Not Authorized."]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        /** @var DiscordEventWebhook $discordEventWebhook */
        $discordEventWebhook = DiscordEventWebhook::where("event_id", $eventId)->first();

        if (!$discordEventWebhook) {
            $discordEventWebhook = new DiscordEventWebhook();

            $discordEventWebhook->eventId = $eventId;
            $discordEventWebhook->url = $request->input("webhookUrl");
            $discordEventWebhook->enabled = $request->input("enabled");
        }

        $discordEventWebhook->save();

        DiscordEventWebhookUpdateType::where("discord_event_webhook_id", $discordEventWebhook->id)->delete();

        foreach ($request->input("updateTypes") as $updateType) {
            $discordEventWebhookUpdateType = new DiscordEventWebhookUpdateType();

            $discordEventWebhookUpdateType->discordEventWebhookId = $discordEventWebhook->id;
            $discordEventWebhookUpdateType->typeId = $updateType['typeId'];

            $discordEventWebhookUpdateType->save();
        }

        return response()->json(["message" => "Successfully saved settings."], Response::HTTP_OK);
    }
}
