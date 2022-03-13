<?php

namespace Modules\Match\Jobs;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Event\Entities\DiscordEventWebhook;
use Modules\Event\Entities\DiscordEventWebhookUpdateType;
use Modules\Match\Entities\MatchModel;

class SendMatchCreatedEventToDiscordWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public MatchModel $match;

    /**
     * @param MatchModel $match
     */
    public function __construct(MatchModel $match)
    {
        $this->match = $match;
    }

    public function handle()
    {
        /** @var DiscordEventWebhook $discordEventWebhook */
        $discordEventWebhook = DiscordEventWebhook::where("event_id", $this->match->group->eventId)->first();

        if (!$discordEventWebhook) {
            return;
        }

        if (!$discordEventWebhook->enabled) {
            return;
        }

        $shouldSend = $discordEventWebhook
            ->discordEventWebhookUpdateTypes()
            ->where("type_id", DiscordEventWebhookUpdateType::MATCH_CREATED)
            ->count();

        if (!$shouldSend) {
            return;
        }

        $participants = "";
        foreach ($this->match->participants as $participant) {
            if ($participant->userId) {
                $participants .= ":flag_".strtolower($participant->user->nat).": ".$participant->user->nickname;
            }

            if ($participant->pageId) {
                $participants .= $participant->page->name;
            }

            if (!$participant->userId && !$participant->pageId) {
                $participants .= $participant->name;
            }

            $participants .= "\n";
        }

        $status = "";
        switch($this->match->statusId) {
            case MatchModel::STATUS_UPCOMING:
                $status = ":green_circle: Upcoming";
                break;

            case MatchModel::STATUS_LIVE:
                $status = ":red_circle: Live";
                break;

            case MatchModel::STATUS_ENDED:
                $status = ":white_circle: Ended";
                break;
        }

        $matchDate = new Carbon($this->match->date);
        $date = $matchDate->isoFormat("lll");
        $matchPage = "https://esac.gg/matches/".$this->match->id;
        $color = 5597183;
        $title = $this->match->name;
        $author = [
            "name" => $this->match->group->name
        ];

        $content = [
            "title" => $title,
            "author" => $author,
            "color" => 5597183,
            "description" =>    "Participants: \n **".$participants."** \n
                                Status: **".$status."** \n
                                Date: **".$date."** \n
                                Match page: **".$matchPage."**"
        ];

        /** @var Client $client */
        $client = app(Client::class);

        $response = $client->post($discordEventWebhook->url, [
            RequestOptions::JSON => ["embeds" => [$content]]
        ]);
    }
}
