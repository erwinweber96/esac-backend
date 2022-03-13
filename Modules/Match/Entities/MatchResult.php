<?php


namespace Modules\Match\Entities;


use App\Model\Model;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Modules\Event\Entities\Participant;
use Modules\GameServer\Entities\GameServer;
use Modules\Group\Entities\Format;
use Modules\Map\Entities\Map;

/**
 * Class MatchResult
 * @package Modules\Match\Entities
 *
 * Contains:
 * @property int    $id
 * @property Carbon $createdAt
 * @property Carbon $updatedAt
 * @property string $result
 * @property bool   $isTotalResult
 * @property bool   $pending
 * @property int    $participantId
 * @property int    $matchId
 * @property int    $mapId
 *
 * Has:
 * @property Format                 $format
 * @property Map                    $map
 * @property MatchResultProperty[]  $properties
 * @property GameServer             $gameServer
 *
 * Belongs to:
 * @property MatchModel       $match
 * @property Participant $participant
 */
class MatchResult extends Model
{
    const TABLE_NAME = "match_results";

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->timestamps();
        $table->integer("format_id")->index()->nullable();
        $table->integer("map_id")->index()->nullable();
        $table->integer("game_server_id")->index()->nullable();
        $table->integer("match_id")->index();
        $table->integer("participant_id")->index();
        $table->string("result");
        $table->boolean("is_total_result");
        $table->boolean("pending")->default(true);
    }

    public $fillable = [
        "match_id",
        "participant_id",
        "result",
        "is_total_result",
        "pending"
    ];

    public function match()
    {
        return $this->belongsTo(MatchModel::class, 'match_id');
    }

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }

    public function format()
    {
        return $this->hasOne(Format::class);
    }

    public function map()
    {
        return $this->hasOne(Map::class);
    }

    public function properties()
    {
        return $this->hasMany(MatchResultProperty::class);
    }

    public function gameServer()
    {
        return $this->hasOne(GameServer::class);
    }
}
