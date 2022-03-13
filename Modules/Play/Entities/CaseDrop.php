<?php

namespace Modules\Play\Entities;

use App\Model\Model;
use Illuminate\Database\Schema\Blueprint;
use Modules\User\Entities\User;

/**
 * Class CaseDrop
 * @package Modules\Play\Entities
 *
 * @property int     $id
 * @property int     $caseId
 * @property int     $userId
 * @property boolean $seen
 * @property string  $createdAt
 * @property string  $updatedAt
 *
 * 1     - 30000     - common 60%       ...............................
 * 30001 - 45000     - uncommon 30%     .............
 * 45001 - 49955     - rare 9.91%       .
 * 49956 - 50000     - unique 0.09%
 */
class CaseDrop extends Model
{
    const UNBOXING_COST = 500;
    const DROP_PROBABILITY = 3;

    const TABLE_NAME = "case_drops";
    const DOODLE_CASE_ID = "1";

    const THRESHOLD_COMMON = 30000;
    const THRESHOLD_UNCOMMON = 45000;
    const THRESHOLD_RARE = 49955;
    const THRESHOLD_UNIQUE = 50000;

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->integer("case_id");
        $table->integer("user_id")->index();
        $table->boolean("seen")->default(false);
        $table->timestamps();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
