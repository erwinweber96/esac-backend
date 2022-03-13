<?php


namespace Modules\Map\Entities;


use App\Model\Model;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class MapPoolRotationMap
 * @package Modules\Map\Entities
 *
 * Contains:
 * @property int $id
 * @property int $mxMapId
 *
 * Has:
 *
 *
 * Belongs to:
 * @property MapPoolRotation $mapPoolRotation
 */
class MapPoolRotationMap extends Model
{
    const TABLE_NAME = "map_pool_rotation_maps";

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->timestamps();
        $table->integer("mx_map_id");
        $table->integer("map_pool_rotation_id")->index();
    }

    public function mapPoolRotation()
    {
        return $this->belongsTo(MapPoolRotation::TABLE_NAME);
    }
}
