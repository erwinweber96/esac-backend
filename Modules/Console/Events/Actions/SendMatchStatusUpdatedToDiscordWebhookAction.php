<?php

namespace Modules\Console\Events\Actions;

use Modules\Console\Events\MatchEnded;
use Modules\Match\Jobs\SendMatchStatusUpdatedEventToDiscordWebhook;

class SendMatchStatusUpdatedToDiscordWebhookAction
{
    public function handle(MatchEnded $matchEnded)
    {
        SendMatchStatusUpdatedEventToDiscordWebhook::dispatch($matchEnded->match);
    }
}
