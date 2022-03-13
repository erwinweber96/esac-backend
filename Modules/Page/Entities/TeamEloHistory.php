<?php


namespace Modules\Page\Entities;


use App\Model\Model;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Modules\Match\Entities\MatchModel;

/**
 * Class TeamEloHistory
 * @package Modules\Page\Entities
 *
 * @property int    $id
 * @property int    $matchId
 * @property int    $pageId
 * @property int    $opponentId
 * @property int    $oldElo
 * @property int    $newElo
 * @property Carbon $updatedAt
 * @property Carbon $createdAt
 *
 * @property Page   $page
 * @property MatchModel  $match
 * @property Page   $opponent
 */
class TeamEloHistory extends Model
{
    const TABLE_NAME = "team_elo_history";
    const RELATIONS = [
        "match",
        "opponent"
    ];

    protected $table = self::TABLE_NAME;
    protected $fillable = [
        "match_id",
        "page_id",
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
        $table->integer("page_id")->index();
        $table->integer("opponent_id");
        $table->integer("old_elo");
        $table->integer("new_elo");
        $table->timestamps();
    }

    public function match()
    {
        return $this->belongsTo(MatchModel::class);
    }

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function opponent()
    {
        return $this->belongsTo(Page::class, "opponent_id");
    }
}
