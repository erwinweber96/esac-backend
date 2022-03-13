<?php


namespace Modules\Group\Entities;

use App\Model\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Modules\Event\Entities\Event;
use Modules\Match\Entities\LossCondition;
use Modules\Match\Entities\MatchModel;
use Modules\Match\Entities\MatchEndCondition;
use Modules\Match\Entities\WinCondition;

/**
 * Class Format
 * @package Modules\Group\Entities
 *
 * Contains:
 * @property int    $id
 * @property string $name
 * @property bool   $inheritable
 * @property bool   $areResultsAdditive
 * @property bool   $isGameServerGuarded
 * @property bool   $matchModifiableByParticipants
 * @property bool   $requiresModeratorApproval
 * @property string $description
 * @property int    $typeId
 * @property int    $eventId
 *
 * Has:
 * @property FormatProperty[]|Collection $properties
 * @property MatchSetting[]|Collection   $matchSettings
 * @property WinCondition[]|Collection   $winConditions
 * @property LossCondition[]|Collection  $lossConditions
 * @property FormatType                  $type
 * @property MatchEndCondition           $matchEndCondition
 *
 * Belongs to:
 * @property Group[]|Collection $groups
 * @property MatchModel[]|Collection $matches
 * @property Event              $event
 */
class Format extends Model
{
    const TABLE_NAME = "formats";

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->string("name");
        $table->boolean("inheritable");
        $table->boolean("are_results_additive");
        $table->boolean("is_game_server_guarded");
        $table->boolean("match_modifiable_by_participants");
        $table->boolean("requires_moderator_approval");
        $table->integer("type_id");
        $table->integer("event_id")->index();
        $table->text("description");
    }

    public $fillable = [
        "name",
        'inheritable',
        'are_results_additive',
        'is_game_server_guarded',
        'match_modifiable_by_participants',
        'requires_moderator_approval',
        'type_id',
        'event_id',
        'description'
    ];

    public $timestamps = false;

    public $relations = [
        'type',
        'matchEndCondition',
        'matchSettings'
    ];

    public function groups()
    {
        return $this->belongsToMany(Group::class);
    }

    public function properties()
    {
        return $this->hasMany(FormatProperty::class);
    }

    public function matchSettings()
    {
        return $this->hasMany(MatchSetting::class);
    }

    public function winConditions()
    {
        return $this->hasMany(WinCondition::class);
    }

    public function lossConditions()
    {
        return $this->hasMany(LossCondition::class);
    }

    public function matches()
    {
        return $this->belongsToMany(MatchModel::class, 'format_match', 'match_id');
    }

    public function type()
    {
        return $this->belongsTo(FormatType::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function matchEndCondition()
    {
        return $this->hasOne(MatchEndCondition::class);
    }
}
