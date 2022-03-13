<?php


namespace Modules\Group\Entities;


use App\Model\Model;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class GroupStatus
 * @package Modules\Group\Entities
 *
 * Contains:
 * @property int    $id
 * @property string $name
 * @property Carbon $createdAt
 * @property Carbon $updatedAt
 *
 * Has:
 *
 *
 * Belongs to:
 * @property Group $group
 */
class GroupStatus extends Model
{
    const TABLE_NAME = "group_status";

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->string("name");
        $table->timestamps();
        $table->integer("group_id")->index();
    }

    public function group()
    {
        $this->belongsTo(Group::class);
    }
}
