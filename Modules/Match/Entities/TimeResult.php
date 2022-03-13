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
 * Class TimeResult
 * @package Modules\Match\Entities
 *
 * Contains:
 * @property int    $id
 * @property Carbon $createdAt
 * @property Carbon $updatedAt
 * @property string $time
 * @property int    $participantId
 * @property int    $matchId
 * @property int    $mapId
 *
 * Belongs to:
 * @property MatchModel       $match
 * @property Participant $participant
 * @property Map         $map
 */
class TimeResult extends Model
{
    const TABLE_NAME = "time_results";

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->timestamps();
        $table->string("time");
        $table->integer("participant_id")->index();
        $table->integer("match_id")->index();
        $table->integer("map_id")->index();
    }
}
