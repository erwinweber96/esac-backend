<?php

namespace Modules\Event\Entities;

use App\Model\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class DiscordEventWebhook
 * @package Modules\Event\Entities
 *
 * @property int     $id
 * @property string  $url
 * @property boolean $enabled
 * @property int     $eventId
 *
 * @property DiscordEventWebhookUpdateType[]|Collection $discordEventWebhookUpdateTypes
 */
class DiscordEventWebhook extends Model
{
    const TABLE_NAME = "discord_event_webhooks";

    public $timestamps = false;

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->string("url");
        $table->boolean("enabled");
        $table->integer("event_id");
    }

    public function discordEventWebhookUpdateTypes()
    {
        return $this->hasMany(DiscordEventWebhookUpdateType::class);
    }
}
