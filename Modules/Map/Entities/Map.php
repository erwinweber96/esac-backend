<?php


namespace Modules\Map\Entities;


use App\Model\Model;
use Illuminate\Database\Schema\Blueprint;


/**
 * Class Map
 * @package Modules\Map\Entities
 *
 * Contains:
 * @property int    $id
 * @property string $name
 * @property int    $mxId
 *
 * Has:
 *
 *
 * Belongs to:
 * @property MapPool $mapPool
 */
class Map extends Model
{
    const TABLE_NAME = "maps";

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->string("name");
        $table->integer("mx_id");
        $table->integer("map_pool_id")->index();
    }

    public function mapPool()
    {
        return $this->belongsTo(MapPool::class);
    }
}
