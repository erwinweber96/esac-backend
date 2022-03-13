<?php


namespace Modules\Event\Entities;


use App\Model\Model;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class EventRole
 * @package Modules\Event\Entities
 *
 * Contains:
 * @property int    $id
 * @property string $name
 * @property int    $moderatorId
 * @property int    $eventId
 *
 * Has:
 *
 *
 * Belongs to:
 * @property Event              $event
 * @property EventModerator     $moderator
 */
class EventModeratorRole extends Model
{
    const TABLE_NAME = "event_moderator_roles";

    const CREATE_POSTS              = "create_posts";
    const CREATE_FORMAT             = "create_format";
    const CREATE_GROUP              = "create_group";
    const ADD_MAP_POOL              = "add_map_pool";
    const CREATE_FAQ                = "create_faq";
    const CHANGE_EVENT_STATUS       = "change_event_status";
    const REMOVE_PARTICIPANTS       = "remove_participants";
    const EDIT_GROUP                = "edit_group";
    const DELETE_GROUP              = "delete_group";
    const CREATE_MATCH              = "create_match";
    const EDIT_GROUP_FORMATS        = "edit_group_formats";
    const EDIT_GROUP_PARTICIPANTS   = "edit_group_participants";
    const EDIT_MATCH                = "edit_match";
    const DELETE_MATCH              = "delete_match";
    const ADD_TOTAL_RESULT          = "add_total_result";
    const ADD_GAME_SERVER           = "add_game_server";
    const ADD_LIVE_STREAM           = "add_live_stream";
    const ADD_VOD                   = "add_vod";
    const SUBMIT_TOTAL_RESULT       = "submit_total_result";
    const SUBMIT_GAME_SERVER        = "submit_game_server";
    const SUBMIT_LIVE_STREAM        = "submit_live_stream";
    const SUBMIT_VOD                = "submit_vod";
    const EDIT_MATCH_PARTICIPANTS   = "edit_match_participants";
    const EDIT_MATCH_FORMATS        = "edit_match_formats";
    const MODERATE_RESULTS          = "moderate_results";
    const MODERATE_GAME_SERVERS     = "moderate_game_servers";
    const MODERATE_LIVE_STREAMS     = "moderate_live_streams";
    const MODERATE_VODS             = "moderate_vods";
    const EDIT_FORMAT               = "edit_format";
    const DELETE_FORMAT             = "delete_format";
    const EDIT_MAP_POOL             = "edit_map_pool";
    const DELETE_MAP_POOL           = "delete_map_pool";

    const ROLES = [
       self::CREATE_POSTS,
       self::CREATE_FORMAT,
       self::CREATE_GROUP,
       self::ADD_MAP_POOL,
       self::CREATE_FAQ,
       self::CHANGE_EVENT_STATUS,
       self::REMOVE_PARTICIPANTS,
       self::EDIT_GROUP,
       self::DELETE_GROUP,
       self::CREATE_MATCH,
       self::EDIT_GROUP_FORMATS,
       self::EDIT_GROUP_PARTICIPANTS,
       self::EDIT_MATCH,
       self::DELETE_MATCH,
       self::ADD_TOTAL_RESULT,
       self::ADD_GAME_SERVER,
       self::ADD_LIVE_STREAM,
       self::ADD_VOD,
       self::SUBMIT_TOTAL_RESULT,
       self::SUBMIT_GAME_SERVER,
       self::SUBMIT_LIVE_STREAM,
       self::SUBMIT_VOD,
       self::EDIT_MATCH_PARTICIPANTS,
       self::EDIT_MATCH_FORMATS,
       self::MODERATE_RESULTS,
       self::MODERATE_GAME_SERVERS,
       self::MODERATE_LIVE_STREAMS,
       self::MODERATE_VODS,
       self::EDIT_FORMAT,
       self::DELETE_FORMAT,
       self::EDIT_MAP_POOL,
       self::DELETE_MAP_POOL
    ];

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public $fillable = [
        "name",
        "event_id",
        "moderator_id"
    ];

    public $timestamps = false;

    public function generateInitialBlueprint(Blueprint $table)
    {
        // TODO: event_moderator_event_role pivot table
        $table->id();
        $table->string("name");
        $table->integer("event_id")->index();
        $table->integer("moderator_id")->index();
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function moderator()
    {
        return $this->belongsTo(EventModerator::class);
    }
}
