<?php


namespace Modules\User\Entities;


use App\Model\Model;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class FriendRequest
 * @package Modules\User\Entities
 *
 * @property int    $id
 * @property int    $fromUserId
 * @property int    $toUserId
 * @property int    $statusId
 * @property string $createdAt
 * @property string $updatedAt
 *
 * @property User   $fromUser
 * @property User   $toUser
 */
class FriendRequest extends Model
{
    const TABLE_NAME = "friend_requests";

    const PENDING  = 1;
    const ACCEPTED = 2;
    const REJECTED = 3;

    protected $fillable = ["from_user_id", "to_user_id", "created_at", "updated_at"];

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->integer("from_user_id")->index();
        $table->integer("to_user_id")->index();
        $table->integer("status_id")->default(self::PENDING);
        $table->timestamps();
    }

    public function fromUser()
    {
        return $this->belongsTo(User::class, "from_user_id");
    }

    public function toUser()
    {
        return $this->belongsTo(User::class, "to_user_id");
    }
}
