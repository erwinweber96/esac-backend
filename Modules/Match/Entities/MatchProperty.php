<?php


namespace Modules\Match\Entities;


use App\Model\Model;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class MatchProperty
 * @package Modules\Match\Entities
 *
 * Contains:
 * @property int    $id
 * @property string $key
 * @property string $value
 * @property bool   $readOnly
 * @property int    $matchId
 *
 * Has:
 *
 *
 *
 * Belongs to:
 * @property MatchModel $match
 */
class MatchProperty extends Model
{
    const TABLE_NAME = "match_properties";

    const PROMOTION_MATCH_ID = "promotion_match_id";
    const DEMOTION_MATCH_ID = "demotion_match_id";

    public $timestamps = false;

    protected $fillable = [
        "key",
        "value",
        "read_only",
        "match_id"
    ];

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->string("key");
        $table->string("value");
        $table->boolean("read_only");
        $table->integer("match_id")->index();
    }

    public function match()
    {
        return $this->belongsTo(MatchModel::class, 'id');
    }
}
