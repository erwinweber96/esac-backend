<?php


namespace Modules\Group\Entities;


use App\Model\Model;
use Illuminate\Database\Schema\Blueprint;
use Modules\Event\Entities\Participant;

/**
 * Class GroupResult
 * @package Modules\Group\Entities
 *
 * @property int    $id
 * @property int    $participantId
 * @property string $result
 * @property string $prize
 * @property int    $position
 * @property int    $groupId
 * @property string $createdAt
 * @property string $updatedAt
 *
 * @property Participant $participant
 * @property Group       $group
 */
class GroupResult extends Model
{
    const TABLE_NAME = "group_results";

    protected $fillable = [
        "participant_id",
        "result",
        "prize",
        "position",
        "group_id",
        "updated_at"
    ];

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->integer("participant_id")->index();
        $table->string("result");
        $table->string("prize");
        $table->integer("position");
        $table->integer("group_id")->index();
        $table->timestamps();
    }

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
