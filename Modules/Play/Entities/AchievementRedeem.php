<?php


namespace Modules\Play\Entities;


use App\Model\Model;
use Illuminate\Database\Schema\Blueprint;
use Modules\User\Entities\User;

/**
 * Class AchievementRedeem
 * @package Modules\Play\Entities
 *
 * @property int    $id
 * @property int    $achievementId
 * @property int    $userId
 * @property string $createdAt
 * @property string $updatedAt
 */
class AchievementRedeem extends Model
{
    const TABLE_NAME = "achievement_redeems";

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->integer("achievement_id");
        $table->integer("user_id")->index();
        $table->timestamps();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
