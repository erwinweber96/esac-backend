<?php


namespace Modules\Game\Entities;


use App\Model\Model;
use Illuminate\Database\Schema\Blueprint;
use Modules\User\Entities\User;

/**
 * Class TrackmaniaAuth
 * @package Modules\Game\Entities
 *
 * @property int    $id
 * @property int    $userId
 * @property string $accountId
 * @property string $displayName
 * @property string $updatedAt
 * @property string $createdAt
 */
class TrackmaniaAuth extends Model
{
    const TABLE_NAME = "trackmania_auths";

    protected $fillable = [
        "user_id",
        "account_id",
        "display_name"
    ];

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->integer("user_id")->index();
        $table->string("account_id");
        $table->string("display_name");
        $table->timestamps();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
