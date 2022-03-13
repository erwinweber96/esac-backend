<?php


namespace Modules\Event\Entities;


use App\Model\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Modules\Game\Entities\Game;
use Modules\Group\Entities\Group;
use Modules\Group\Entities\GroupResult;
use Modules\Match\Entities\MatchModel;
use Modules\Match\Entities\MatchResult;
use Modules\Page\Entities\Page;
use Modules\User\Entities\User;

/**
 * Class Participant
 * @package Modules\Event\Entities
 *
 * Contains:
 * @property int    $id
 * @property string $type
 * @property bool   $pending
 * @property int    $pageId
 * @property int    $eventId
 * @property string $name
 * @property int    $userId
 *
 * Has:
 * @property MatchResult[]  $results
 * @property User           $user
 * @property Page           $page
 * @property Lineup[]       $lineups
 *
 * Belongs to:
 * @property MatchModel[]|Collection    $matches
 * @property Event                      $event
 * @property Group[]|Collection         $groups
 */
class Participant extends Model
{
    const TYPE_USER     = "user";
    const TYPE_TEAM     = "page";
    const TYPE_NON_USER = "non_user";

    const TYPES = [
        self::TYPE_USER,
        self::TYPE_TEAM,
        self::TYPE_NON_USER
    ];

    const TABLE_NAME = "participants";

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->string("type");
        $table->integer("user_id")->index();
        $table->integer("page_id")->index();
        $table->integer("event_id")->index();
    }

    public $fillable = [
        "type",
        "user_id",
        "page_id",
        "event_id",
        "pending"
    ];

    public $relations = [
        'user',
        'page',
        'event',
        'lineups'
    ];

    public function matches()
    {
        return $this->belongsToMany(MatchModel::class, 'match_participant', "participant_id", "match_id");
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class);
    }

    public function results()
    {
        return $this->hasMany(MatchResult::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function inGameAccount()
    {
        switch ($this->event->game->name) {
            case Game::TRACKMANIA:
                return $this->user->tmNickname;
            case Game::TRACKMANIA_2_STADIUM:
                return $this->user->maniaplanet->login;
        }

        return "";
    }

    public function lineups()
    {
        return $this->hasMany(Lineup::class);
    }

    public function groupResults()
    {
        return $this->hasMany(GroupResult::class);
    }
}
