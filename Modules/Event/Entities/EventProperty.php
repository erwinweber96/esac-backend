<?php


namespace Modules\Event\Entities;


use App\Model\Model;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class EventProperty
 * @package Modules\Event\Entities
 *
 * Contains:
 * @property int    $id
 * @property string $key
 * @property string $value
 * @property bool   $readOnly
 * @property int    $eventId
 *
 * Has:
 *
 *
 * Belongs to:
 * @property Event  $event
 */
class EventProperty extends Model
{
    const TABLE_NAME            = "event_properties";

    const NON_PARTICIPANT       = "non_participant";
    const FRANCE_BASED          = "france_based";
    const LINEUP_CHANGE_ALLOWED = "lineup_change_allowed";
    const NEXT_MATCH_NAME       = "next_match_name";
    const NEXT_MATCH_TIMESTAMP  = "next_match_timestamp";
    const PLAY_ESAC_GG_EVENT    = "play_esac_gg_event";
    const PARTICIPANTS_LIMIT    = "participants_limit";
    const PLAY_MAP_NAME         = "play_map_name";
    const PLAY_MAP_URL          = "play_map_url";
    const PLAY_MAP_MX_ID        = "play_map_mx_id";
    const DISCORD_REQUIRED      = "discord_required";
    const PENDING_REGISTRATION  = "pending_registration";
    const WEEKLY_EVENT_MX_ID    = "weekly_event_mx_id";
    const WEEKLY_EVENT          = "weekly_event";
    const HOURLY_SHOWDOWN       = "hourly_showdown";
    const RANKED_EVENT          = "ranked_event";
    const MATCHMAKING_LADDER    = "matchmaking_ladder";
    const TWITCH_FOLLOWER_ONLY  = "twitch_follower_only";
    const TWITCH_SUBSCRIBER_ONLY      = "twitch_subscriber_only";
    const MULTI_MAP_SEEDING_PHASE     = "multi_map_seeding_phase";
    const NUMBER_OF_QUALIFIED_PLAYERS = "number_of_qualified_players";
    const CUSTOM_SHOWDOWN             = "custom_showdown";
    const BADGE_ID_1131_ACHIEVEMENT   = "badge_id_1131_achievement";

    const PLAY_PROPERTIES = [
        self::PLAY_MAP_NAME,
        self::PLAY_MAP_URL,
        self::PARTICIPANTS_LIMIT,
        self::HOURLY_SHOWDOWN,
        self::MATCHMAKING_LADDER,
        self::RANKED_EVENT,
        self::CUSTOM_SHOWDOWN
    ];

    const KEYS = [
        self::NON_PARTICIPANT,
        self::FRANCE_BASED,
        self::LINEUP_CHANGE_ALLOWED,
        self::NEXT_MATCH_NAME,
        self::NEXT_MATCH_TIMESTAMP,
        self::PLAY_ESAC_GG_EVENT,
        self::PARTICIPANTS_LIMIT,
        self::PLAY_MAP_URL,
        self::PLAY_MAP_NAME,
        self::DISCORD_REQUIRED,
        self::PENDING_REGISTRATION,
        self::WEEKLY_EVENT_MX_ID,
        self::WEEKLY_EVENT,
        self::MATCHMAKING_LADDER
    ];

    public $timestamps = false;

    protected $fillable = [
        "key",
        "value",
        "read_only",
        "event_id"
    ];

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->string("key");
        $table->string("value");
        $table->boolean("read_only");
        $table->integer("event_id")->index();
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
