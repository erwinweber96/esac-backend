<?php


namespace Modules\Game\Entities;


use App\Model\Model;
use Illuminate\Database\Schema\Blueprint;
use Modules\Event\Entities\Event;
use Modules\GameServer\Entities\GameServer;

/**
 * Class Game
 * @package Modules\Game\Entities
 *
 * Contains:
 * @property int $id
 * @property string $name
 *
 * Has:
 * @property Titlepack[] $titlepacks
 * @property GameServer[] $gameServers
 * @property Event[] $events
 *
 * Belongs to:
 *
 */
class Game extends Model
{
    const TABLE_NAME = "games";

    const TRACKMANIA            = "Trackmania";
    const TRACKMANIA_2_STADIUM  = "TrackMania 2 Stadium";

    const TRACKMANIA_ID           = 1;
    const TRACKMANIA_2_STADIUM_ID = 2;

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->string("name");
    }

    public $fillable = [
        "id",
        "name"
    ];

    public $timestamps = false;

    public function titlepacks()
    {
        return $this->hasMany(Titlepack::class);
    }

    public function events()
    {
        $this->hasMany(Event::class);
    }
}
