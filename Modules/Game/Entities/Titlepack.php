<?php


namespace Modules\Game\Entities;


use App\Model\Model;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class Titlepack
 * @package Modules\Game\Entities
 *
 * Contains:
 * @property int    $id
 * @property string $name
 * @property string $author
 * @property string $urlName
 *
 * Has:
 *
 *
 * Belongs to:
 * @property Game $game
 */
class Titlepack extends Model
{
    const TABLE_NAME = "titlepacks";

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->string("name");
        $table->string("author");
        $table->string("url_name");
        $table->integer("game_id")->index();
    }

    public function game()
    {
        return $this->belongsTo(Game::class);
    }
}
