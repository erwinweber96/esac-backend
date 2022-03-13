<?php


namespace Modules\Play\Entities;


use App\Model\Model;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Modules\Match\Entities\MatchModel;

/**
 * Class EloHistory
 * @package Modules\Play\Entities
 *
 * @property int    $id
 * @property int    $matchId
 * @property int    $userId
 * @property int    $opponentId
 * @property int    $oldElo
 * @property int    $newElo
 * @property Carbon $updatedAt
 * @property Carbon $createdAt
 */
class EloHistory extends Model
{
    const TABLE_NAME = "elo_history";
    const RELATIONS = [
        "match"
    ];

    protected $table = self::TABLE_NAME;
    protected $fillable = [
        "match_id",
        "user_id",
        "opponent_id",
        "old_elo",
        "new_elo",
        "updated_at",
        "created_at"
    ];
    public $relations = self::RELATIONS;

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->integer("match_id")->index();
        $table->integer("user_id")->index();
        $table->integer("opponent_id");
        $table->integer("old_elo");
        $table->integer("new_elo");
        $table->timestamps();
    }

    public function match()
    {
        return $this->belongsTo(MatchModel::class);
    }
}
