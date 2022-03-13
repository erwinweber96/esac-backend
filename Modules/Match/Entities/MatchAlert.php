<?php


namespace Modules\Match\Entities;


use App\Model\Model;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class MatchAlert
 * @package Modules\Match\Entities
 *
 * @property int    $id
 * @property string $message
 * @property string $type
 * @property int    $matchId
 * @property bool   $public
 * @property Carbon $updatedAt
 * @property Carbon $createdAt
 *
 * @property MatchModel  $match
 */
class MatchAlert extends Model
{
    const TABLE_NAME = "match_alerts";

    const TYPE_INFO         = "info";
    const TYPE_PRIMARY      = "primary";
    const TYPE_WARNING      = "warning";
    const TYPE_ERROR        = "danger";
    const TYPE_SECONDARY    = "secondary";

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->text("message");
        $table->string("type");
        $table->integer("match_id")->index();
        $table->boolean("public");
        $table->timestamps();
    }

    public function match()
    {
        return $this->belongsTo(MatchModel::class);
    }
}
