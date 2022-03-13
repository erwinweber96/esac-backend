<?php


namespace Modules\Event\Entities;


use App\Model\Model;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class DiscordEventWebhookUpdateType
 * @package Modules\Event\Entities
 *
 * @property int    $id
 * @property int    $discordEventWebhookId
 * @property int    $typeId
 *
 * @property DiscordEventWebhook $discordEventWebhook
 */
class DiscordEventWebhookUpdateType extends Model
{
    const PARTICIPANT_REGISTERED = 1;
    const MATCH_CREATED          = 2;
    const MATCH_STATUS_UPDATED   = 3;
    const EVENT_STATUS_UPDATED   = 4;

    const TABLE_NAME = "discord_event_webhook_update_types";

    public $timestamps = false;

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->integer("discord_event_webhook_id");
        $table->integer("type_id");
    }

    public function discordEventWebhook()
    {
        return $this->belongsTo(DiscordEventWebhook::class);
    }
}
