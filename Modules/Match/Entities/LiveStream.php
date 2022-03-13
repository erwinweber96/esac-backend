<?php


namespace Modules\Match\Entities;


use App\Model\Model;
use Illuminate\Database\Schema\Blueprint;
use Modules\Link\Entities\Link;

/**
 * Class LiveStream
 * @package Modules\Match\Entities
 *
 * Contains:
 * @property int $id
 * @property Link $link
 *
 * Has:
 *
 *
 * Belongs to:
 * @property MatchModel $match
 */
class LiveStream extends Model
{
    // TODO: check which data is needed from twitch api

    const TABLE_NAME = "live_streams";

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->timestamps();
        $table->integer("link_id")->index();
        $table->integer("match_id")->index();
    }

    public $fillable = [
        "link_id",
        "match_id"
    ];

    public function match()
    {
        return $this->belongsTo(MatchModel::class);
    }

    public function link()
    {
        return $this->belongsTo(Link::class);
    }
}
