<?php


namespace Modules\Game\Entities;


use App\Model\Model;
use Illuminate\Database\Schema\Blueprint;
use Modules\User\Entities\User;

/**
 * Class TrackmaniaAccessToken
 * @package Modules\Game\Entities
 *
 * @property int    $id
 * @property string $accessToken
 * @property int    $userId
 * @property string $createdAt
 * @property string $updatedAt
 *
 * @property User   $user
 */
class TrackmaniaAccessToken extends Model
{
    const TABLE_NAME = "trackmania_access_tokens";

    protected $fillable = ["user_id", "access_token", "updated_at", "created_at"];

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->string("access_token");
        $table->integer("user_id")->index();
        $table->timestamps();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
