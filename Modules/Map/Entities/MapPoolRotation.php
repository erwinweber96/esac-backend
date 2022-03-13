<?php


namespace Modules\Map\Entities;


use App\Model\Model;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Modules\Event\Entities\Event;
use Modules\Group\Entities\Group;

/**
 * Class MapPoolRotation
 * @package Modules\Map\Entities
 *
 * Contains:
 * @property int                    $id
 * @property Carbon                 $startDate
 * @property Carbon                 $endDate
 *
 * Has:
 * @property MapPoolRotationMap[]   $maps
 *
 * Belongs to:
 * @property Group                  $group
 * @property Event                  $event
 */
class MapPoolRotation extends Model
{
    const TABLE_NAME = "map_pool_rotations";

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->timestamps();
        $table->timestamp("start_date")->nullable();
        $table->timestamp("end_date")->nullable();
        $table->integer("group_id")->index()->nullable();
        $table->integer("event_id")->index();
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function maps()
    {
        return $this->hasMany(MapPoolRotationMap::class);
    }
}
