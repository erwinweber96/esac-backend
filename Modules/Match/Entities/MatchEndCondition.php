<?php


namespace Modules\Match\Entities;


use App\Model\Model;
use Illuminate\Database\Schema\Blueprint;
use Modules\Group\Entities\Format;

/**
 * Class MatchEndCondition
 * @package Modules\Match\Entities
 *
 * @property integer $id
 * @property integer $formatId
 * @property integer $minMapsPlayed
 * @property integer $maxMapsPlayed
 * @property integer $pointsReached
 * @property integer $numberOfPlayersWithPointsReached
 *
 * @property Format $format
 */
class MatchEndCondition extends Model
{
    const TABLE_NAME = "match_end_conditions";

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->integer("format_id")->index();
        $table->integer("min_maps_played")->nullable();
        $table->integer("max_maps_played")->nullable();
        $table->integer("points_reached")->nullable();
        $table->integer("number_of_players_with_points_reached")->nullable();
        $table->timestamps();
    }

    protected $fillable = [
        "format_id",
        "min_maps_played",
        "max_maps_played",
        "points_reached",
        "number_of_players_with_points_reached"
    ];

    public function format()
    {
        return $this->belongsTo(Format::class);
    }
}
