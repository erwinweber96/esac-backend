<?php


namespace Modules\Match\Entities;

use App\Model\Model;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Modules\User\Entities\User;

/**
 * Class MatchComment
 * @package Modules\Group\Entities
 *
 * Contains:
 * @property int    $id
 * @property string $text
 * @property Carbon $updatedAt
 * @property Carbon $createdAt
 *
 * Has:
 *
 *
 *
 * Belongs to:
 * @property User $user
 * @property MatchModel $match
 */
class MatchComment extends Model
{
    const TABLE_NAME = "match_comments";

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->text("text");
        $table->timestamps();
        $table->integer("user_id")->index();
        $table->integer("match_id")->index();
    }

    public function match()
    {
        return $this->belongsTo(MatchModel::class, 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
