<?php


namespace Modules\User\Entities;


use App\Model\Model;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class Discord
 * @package Modules\User\Entities
 *
 * @property int    $id
 * @property int    $discordId
 * @property int    $discordNickname
 * @property int    $userId
 *
 * @property User   $user
 */
class Discord extends Model
{
    const TABLE_NAME = "user_discords";

    public $timestamps = false;

    protected $table = self::TABLE_NAME;
    protected $fillable = [
        "discordId",
        "discordNickname"
    ];

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->integer("discord_id");
        $table->string("discord_nickname");
        $table->integer("user_id");
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
