<?php

namespace Modules\Event\Jobs;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Event\Entities\DiscordEventWebhook;
use Modules\Event\Entities\DiscordEventWebhookUpdateType;
use Modules\Event\Entities\Event;
use Modules\Event\Entities\Participant;

class SendParticipantRegisteredEventToDiscordWebhook  implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Participant $participant;
    public Event $event;

    /**
     * @param Participant $participant
     * @param Event $event
     */
    public function __construct(Participant $participant, Event $event)
    {
        $this->participant = $participant;
        $this->event = $event;
    }

    public function handle()
    {
        /** @var Client $client */
        $client = app(Client::class);

        /** @var DiscordEventWebhook $discordEventWebhook */
        $discordEventWebhook = DiscordEventWebhook::where("event_id", $this->event->id)
            ->first();

        if (!$discordEventWebhook->enabled) {
            return;
        }

        $shouldSend = $discordEventWebhook
            ->discordEventWebhookUpdateTypes()
            ->where("type_id", DiscordEventWebhookUpdateType::PARTICIPANT_REGISTERED)
            ->count();

        if (!$shouldSend) {
            return;
        }

        if ($this->participant->userId) {
            $title = ":flag_".strtolower($this->participant->user->nat).": " . $this->participant->user->nickname . " registered.";
        }

        if ($this->participant->pageId) {
            $title = $this->participant->page->name . " registered.";
        }

        if (!$this->participant->userId && !$this->participant->pageId) {
            $title = $this->participant->name . " registered.";
        }

        $description = "You can view all participants [here](https://esac.gg/events/".$this->event->slug."/participants).";

        $content = [
            "title" => $title,
            "description" => $description,
            "color" => 5597183
        ];

        $response = $client->post($discordEventWebhook->url, [
            RequestOptions::JSON => ["embeds" => [$content]]
        ]);
    }
}
