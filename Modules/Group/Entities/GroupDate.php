<?php


namespace Modules\Group\Entities;

use App\Model\Model;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class GroupDate
 * @package Modules\Group\Entities
 *
 * Contains:
 * @property int    $id
 * @property string $name
 * @property Carbon $date
 * @property bool   $isStartDate
 * @property bool   $isEndDate
 * @property bool   $isActionDate
 *
 * Has:
 *
 *
 * Belongs to:
 * @property Group  $group
 */
class GroupDate extends Model
{
    const TABLE_NAME = "group_dates";

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->string("name");
        $table->timestamp("date");
        $table->boolean("is_start_date");
        $table->boolean("is_end_date");
        $table->boolean("is_action_date");
        $table->integer("group_id")->index();
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
