<?php


namespace Modules\Event\Entities;


use App\Model\Model;
use Illuminate\Database\Schema\Blueprint;
use Modules\User\Entities\User;

/**
 * Class EventModerator
 * @package Modules\Event\Entities
 *
 * Contains:
 * @property int $id
 * @property int $userId
 * @property int $eventId
 *
 * Has:
 * @property User                 $user
 * @property EventModeratorRole[] $roles
 *
 * Belongs to:
 * @property Event $event
 */
class EventModerator extends Model
{
    const TABLE_NAME = "event_moderators";

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->integer("user_id")->index();
        $table->integer("event_id")->index();
    }

    public $fillable = [
        "user_id",
        "event_id"
    ];

    public $timestamps = false;

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function roles()
    {
        return $this->hasMany(EventModeratorRole::class, "moderator_id");
    }
}
