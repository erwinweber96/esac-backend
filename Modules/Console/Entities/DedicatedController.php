<?php


namespace Modules\Console\Entities;


use App\Model\Model;
use Illuminate\Database\Schema\Blueprint;
use Modules\Match\Entities\MatchModel;

/**
 * Class DedicatedController
 * @package Modules\Console\Entities
 *
 * @property integer    $id
 * @property integer    $statusId
 * @property integer    $port       JS PORT
 * @property integer    $matchId
 *
 * @property MatchModel      $match
 */
class DedicatedController extends Model
{
    const TABLE_NAME = "dedicated_controllers";

    const OPEN = 1;
    const RESERVED = 2;
    const CONFIGURING = 3;
    const PRE_MATCH = 4;
    const LIVE = 5;
    const CLOSED = 6;

    protected $fillable = [
        "port",
        "status_id",
        "match_id",
        "updated_at",
        "created_at"
    ];

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->integer("status_id")->default(self::OPEN);
        $table->integer("port");
        $table->integer("match_id")->nullable();
        $table->timestamps();
    }

    public function match()
    {
        return $this->belongsTo(MatchModel::class);
    }
}
