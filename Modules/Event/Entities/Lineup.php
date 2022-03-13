<?php


namespace Modules\Event\Entities;


use App\Model\Model;
use Illuminate\Database\Schema\Blueprint;
use Modules\Page\Entities\PageMember;
use Modules\User\Entities\User;

/**
 * Class Lineup
 * @package Modules\Event\Entities
 *
 * @property int $id
 * @property int $participantId
 * @property int $pageMemberId
 * @property int $userId
 *
 * @property Participant $participant
 * @property PageMember  $pageMember
 * @property User        $user
 */
class Lineup extends Model
{
    const TABLE_NAME = "lineups";

    protected $fillable = [
        "participant_id",
        "page_member_id",
        "user_id"
    ];

    public $timestamps = false;

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->integer("participant_id")->index();
        $table->integer("page_member_id")->index();
    }
    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }

    public function pageMember()
    {
        return $this->belongsTo(PageMember::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
