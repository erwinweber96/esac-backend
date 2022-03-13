<?php


namespace Modules\Map\Entities;


use App\Model\Model;
use Illuminate\Database\Schema\Blueprint;
use Modules\Event\Entities\Event;
use Modules\Game\Entities\Game;
use Modules\Map\ManiaExchange\Entities\Mappack;
use Modules\Map\ManiaExchange\Repositories\MappackRepository;
use Modules\Map\ManiaExchange\Repositories\MappackRepositoryInterface;
use Modules\Map\ManiaExchange\Repositories\TMXMappackRepository;
use Modules\Match\Entities\MatchModel;

/**
 * Class MapPool
 * @package Modules\Map\Entities
 *
 * Contains:
 * @property int     $id
 * @property string  $name
 * @property int     $mxId
 * @property boolean $custom
 * @property string  $link
 * @property int     $eventId
 *
 * Has:
 * @property Map[]      $maps
 * @property Mappack    $mxData
 *
 * Belongs to:
 * @property MatchModel[] $matches
 * @property Event   $event
 */
class MapPool extends Model
{
    const TABLE_NAME = "map_pools";

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->string("name");
        $table->integer("mx_id")->index();
        $table->integer("event_id")->index();
    }

    public $fillable = [
        'name',
        'mx_id',
        'event_id',
        'custom',
        'link'
    ];

    public $appends = [
        'mxData'
    ];

    public $timestamps = false;

    public function maps()
    {
        return $this->hasMany(Map::class);
    }

    public function matches()
    {
        return $this->hasMany(MatchModel::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function getMxDataAttribute()
    {
        if (!$this->mxId) {
            return null;
        }


        switch($this->event->gameId) {
            case Game::TRACKMANIA_ID :
                /** @var MappackRepositoryInterface $repository */
                $repository = app(TMXMappackRepository::class);
                break;

            case Game::TRACKMANIA_2_STADIUM_ID :
                /** @var MappackRepositoryInterface $repository */
                $repository = app(MappackRepository::class);
                break;

            default:
                /** @var MappackRepositoryInterface $repository */
                $repository = app(TMXMappackRepository::class);
                break;
        }

        /** @var Mappack $mappack */
        $mappack = $repository->findById($this->mxId);

        return $mappack;
    }
}
