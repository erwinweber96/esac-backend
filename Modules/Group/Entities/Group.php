<?php


namespace Modules\Group\Entities;

use App\Model\Model;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Modules\Event\Entities\Event;
use Modules\Event\Entities\Participant;
use Modules\Map\Entities\MapPoolRotation;
use Modules\Match\Entities\MatchModel;

/**
 * Class Group
 * @package Modules\Group\Entities
 *
 * Contains:
 * @property int     $id
 * @property string  $name
 * @property int     $minSize
 * @property int     $maxSize
 * @property string  $type
 * @property Carbon  $createdAt
 * @property Carbon  $updatedAt
 * @property boolean $isTypeTree
 * @property int     $eventId
 * @property boolean $private
 * @property int     $groupContainerId
 *
 * Has:
 * @property Format[]|Collection            $formats
 * @property MatchModel[]|Collection             $matches
 * @property GroupDate[]|Collection         $dates
 * @property MapPoolRotation[]|Collection   $mapPoolRotations
 * @property GroupStatus                    $status
 * @property GroupProperty[]|Collection     $properties
 * @property Participant[]|Collection       $participants
 *
 * Belongs to:
 * @property Event $event
 * @property GroupContainer $groupContainer
 */
class Group extends Model
{
    const TABLE_NAME = "groups";

    const TYPE_GENERIC = "Generic";
    const TYPE_RESULT  = "Result";

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->string("name");
        $table->integer("min_size");
        $table->integer("max_size");
        $table->string("type");
        $table->timestamps();
        $table->integer("event_id")->index();
    }

    protected $fillable = [
        "name",
        "min_size",
        "max_size",
        "type",
        "event_id",
        "formats",
        "private",
        "group_container_id"
    ];

    public $relations = [
        'formats',
        'matches',
        'participants',
        'participants.user',
        'participants.page',
        'matches.participants.page',
        'matches.participants.user',
        'matches.formats'
    ];

    public function formats()
    {
        return $this->belongsToMany(Format::class);
    }

    public function matches()
    {
        return $this->hasMany(MatchModel::class);
    }

    public function dates()
    {
        return $this->hasMany(GroupDate::class);
    }

    public function mapPoolRotations()
    {
        return $this->hasMany(MapPoolRotation::class);
    }

    public function status()
    {
        return $this->hasOne(GroupStatus::class);
    }

    public function properties()
    {
        return $this->hasMany(GroupProperty::class);
    }

    public function participants()
    {
        return $this->belongsToMany(Participant::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function groupContainer()
    {
        return $this->belongsTo(GroupContainer::class);
    }
}
