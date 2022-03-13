<?php


namespace Modules\GameServer\Entities;


use App\Model\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Modules\Event\Entities\Event;
use Modules\Game\Entities\Game;
use Modules\Match\Entities\MatchModel;

/**
 * Class GameServer
 * @package Modules\GameServer\Entities
 *
 * Contains:
 * @property int    $id
 * @property string $name
 * @property string $ip
 * @property int    $port
 * @property string $url
 * @property string $password
 * @property bool   $pending
 *
 * Has:
 * @property Game               $game
 * @property GameServerStatus   $status
 *
 * Belongs to:
 * @property MatchModel[]|Collection $matches
 * @property Event              $event
 */
class GameServer extends Model
{
    const TABLE_NAME = "game_servers";

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->timestamps();
        $table->string("name");
        $table->string("ip")->nullable();
        $table->integer("port")->nullable();
        $table->string("url");
        $table->string("password")->nullable();
        $table->integer("game_id")->nullable()->index();
        $table->integer("event_id")->nullable()->index();
        $table->boolean("pending")->default(true);
        // TODO: status
        // TODO: game_server_match pivot
    }

    public $fillable = [
        "name",
        "ip",
        "port",
        "url",
        "password",
        "game_id",
        "event_id",
        "pending"
    ];

    public function matches()
    {
        return $this->belongsToMany(MatchModel::class, 'game_server_match', 'game_server_id', 'match_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
