<?php

namespace Modules\Play\Entities;

use App\Model\Model;
use Illuminate\Database\Schema\Blueprint;

/**
 * @property int        $id
 * @property int        $userId
 * @property int        $matchId
 * @property boolean    $hasWon
 * @property string     $createdAt
 * @property string     $updatedAt
 */
class PlayerMatchStream extends Model
{
    const TABLE_NAME = "player_match_streams";

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->integer("user_id")->index();
        $table->integer("match_id")->index();
        $table->timestamps();
    }
}
