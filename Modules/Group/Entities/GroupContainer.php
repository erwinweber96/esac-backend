<?php


namespace Modules\Group\Entities;


use App\Model\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Modules\Event\Entities\Event;

/**
 * Class GroupContainer
 * @package Modules\Group\Entities
 *
 * @property int        $id
 * @property string     $name
 * @property int        $typeId
 * @property int        $eventId
 * @property boolean    $public
 * @property string     $createdAt
 * @property string     $updatedAt
 *
 * @property Group[]|Collection $groups
 * @property Event              $event
 */
class GroupContainer extends Model
{
    const TABLE_NAME = "group_containers";

    const TYPE_GENERIC              = 1;
    const TYPE_SINGLE_ELIMINATION   = 2;
    const TYPE_DOUBLE_ELIMINATION   = 3;
    const TYPE_ROUND_ROBIN          = 4;
    const TYPE_SWISS                = 5;

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->string("name");
        $table->integer("type_id");
        $table->integer("event_id");
        $table->boolean("public");
        $table->timestamps();
    }

    public function groups()
    {
        return $this->hasMany(Group::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
