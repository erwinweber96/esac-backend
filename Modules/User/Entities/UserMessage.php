<?php


namespace Modules\User\Entities;


use App\Model\Model;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Modules\Match\Entities\MatchModel;

/**
 * Class UserMessage
 * @package Modules\User\Entities
 *
 * @property int            $id
 * @property int            $fromUserId
 * @property int            $toUserId
 * @property string         $message
 * @property string         $channel
 * @property string         $type
 * @property Carbon         $updatedAt
 * @property Carbon         $createdAt
 *
 * @property MatchModel  $match
 * @property User   $fromUser
 * @property User   $toUser
 */
class UserMessage extends Model
{
    const TABLE_NAME = "user_messages";
    const TYPE_MESSAGE = "msg";
    const TYPE_CHALLENGE = "challenge";
    const TYPE_MATCH = "match";

    public $appends = ["match"];

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->integer("from_user_id")->index();
        $table->integer("to_user_id")->index();
        $table->text("message");
        $table->string("channel")->nullable();
        $table->string("type")->default(self::TYPE_MESSAGE);
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

    public function getMatchAttribute()
    {
        if ($this->type != self::TYPE_MATCH) {
            return null;
        }

        $message = json_decode($this->message);
        $matchId = $message->id;

        return MatchModel::where("id", $matchId)->first();
    }
}
