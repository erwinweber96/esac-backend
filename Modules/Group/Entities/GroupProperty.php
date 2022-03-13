<?php


namespace Modules\Group\Entities;


use App\Model\Model;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class GroupProperty
 * @package Modules\Group\Entities
 *
 * Contains:
 * @property int    $id
 * @property string $key
 * @property string $value
 * @property bool   $readOnly
 * @property int    $groupId
 *
 * Has:
 *
 *
 * Belongs to:
 * @property Group  $group
 */
class GroupProperty extends Model
{
    const TABLE_NAME = "group_properties";

    const POINTS_PER_WIN = "points_per_win";
    const POINTS_PER_LOSS = "points_per_loss";
    const POINTS_PER_DRAW = "points_per_draw";
    const WON_UNTIL_QUALIFICATION = "won_until_qualification";
    const LOST_UNTIL_ELIMINATION = "lost_until_elimination";
    const ELIMINATED = "eliminated";
    const QUALIFIED = "qualified";

    public $timestamps = false;

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
        $table->integer("group_id")->index();
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
