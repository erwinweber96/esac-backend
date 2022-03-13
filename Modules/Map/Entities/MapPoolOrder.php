<?php


namespace Modules\Map\Entities;


use App\Model\Model;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Modules\Match\Entities\MatchModel;

/**
 * Class MapPoolOrder
 * @package Modules\Map\Entities
 *
 * @property int $id
 * @property int $mapPoolId
 * @property int $mxMapId
 * @property int $order
 * @property int $matchId
 *
 * @property MapPool $mapPool
 * @property MatchModel   $match
 * @property Carbon  $createdAt
 * @property Carbon  $updatedAt
 */
class MapPoolOrder extends Model
{
    const TABLE_NAME = "map_pool_orders";

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    protected $fillable = [
        "map_pool_id",
        "mx_map_id",
        "order",
        "match_id"
    ];

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->integer("map_pool_id")->index();
        $table->integer("mx_map_id");
        $table->integer("order");
        $table->integer("match_id")->index();
        $table->timestamps();
    }

    public function mapPool()
    {
        return $this->belongsTo(MapPool::class);
    }

    public function match()
    {
        return $this->belongsTo(MatchModel::class);
    }
}
