<?php


namespace Modules\Match\Entities;

use App\Model\Model;
use Illuminate\Database\Schema\Blueprint;
use Modules\Link\Entities\Link;

/**
 * Class Vod
 * @package Modules\Match\Entities
 *
 * Vods represent links towards the replays of one or more matches.
 *
 * Has:
 * @property int     $id
 * @property string  $about
 *
 * Has:
 * @property Link $link
 *
 * Belongs to:
 * @property MatchModel $match
 */
class Vod extends Model
{
    const TABLE_NAME = "vods";

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->timestamps();
        $table->text("about");
        $table->integer("link_id")->index();
        $table->integer("match_id")->index();
    }

    public $fillable = [
        "about",
        "link_id",
        "match_id"
    ];

    public function match()
    {
        return $this->belongsTo(MatchModel::class, 'id');
    }

    public function link()
    {
        return $this->belongsTo(Link::class);
    }
}
