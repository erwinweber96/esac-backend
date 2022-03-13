<?php


namespace Modules\Event\Entities;


use App\Model\Model;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Modules\Game\Entities\Game;
use Modules\GameServer\Entities\GameServer;
use Modules\Group\Entities\Format;
use Modules\Group\Entities\Group;
use Modules\Link\Entities\Link;
use Modules\Map\Entities\MapPool;
use Modules\Match\Entities\MatchModel;
use Modules\Page\Entities\Page;
use Modules\Post\Entities\Post;

/**
 * Class Event
 * @package Modules\Event\Entities
 *
 * Contains:
 * @property int     $id
 * @property string  $name
 * @property string  $slug
 * @property string  $about
 * @property string  $type
 * @property Carbon  $createdAt
 * @property Carbon  $updatedAt
 * @property boolean $isTeamEvent
 * @property boolean $private
 * @property int     $statusId
 * @property boolean $registrationOpen
 * @property boolean $requiredGameAccount
 * @property int     $gameId
 * @property boolean $isVerified
 * @property int     $pageId
 *
 * Has:
 * @property Participant[]|Collection        $participants
 * @property EventProperty[]|Collection      $properties
 * @property Link[]|Collection               $links
 * @property EventDate[]|Collection          $dates
 * @property EventModeratorRole[]|Collection $roles
 * @property EventModerator[]|Collection     $moderators
 * @property Post[]|Collection               $posts
 * @property Group[]|Collection              $groups
 * @property MapPool[]|Collection            $mapPools
 * @property GameServer[]|Collection         $gameServers
 * @property EventFaq[]|Collection           $faq
 * @property Format[]|Collection             $formats
 *
 * @property array                $featuredStream
 *
 * Belongs to:
 * @property Page $page
 * @property Game $game
 */
class Event extends Model
{
    const TABLE_NAME = "events";

    const STATUS_OPEN        = 1;
    const STATUS_UPCOMING    = 1;
    const STATUS_LIVE        = 2;
    const STATUS_ENDED       = 3;
    const STATUS_CONFIGURING = 4;

    const ALL_STATUS = [
        self::STATUS_OPEN,
        self::STATUS_UPCOMING,
        self::STATUS_LIVE,
        self::STATUS_ENDED,
        self::STATUS_CONFIGURING
    ];

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->string("name");
        $table->string("slug")->index();
        $table->text("about");
        $table->string("type");
        $table->timestamps();
        $table->boolean("is_team_event");
        $table->integer("page_id")->index();
        $table->integer("game_id")->index();
        $table->integer("status_id")->default(self::STATUS_OPEN);
    }

    protected $fillable = [
        'name',
        'slug',
        'about',
        'type',
        'page_id',
        'is_team_event',
        'game_id',
        'status_id',
        'private',
        'registration_open',
        'required_game_account'
    ];

    const RELATIONS =  [
        'game',
        'page',
        'page.user',
        'formats',
        'mapPools',
        'participants',
        'groups.participants',
        'groups.participants.user',
        'groups.participants.page',
        'posts',
        'faq',
        'page.members',
        'page.members.user',
        'moderators',
        'moderators.user',
        'moderators.roles',
        'links',
        'dates'
    ];

    public $relations = self::RELATIONS;

    public $appends = [
        "featuredStream",
        "featuredMatches"
    ];

    public function participants()
    {
        return $this->hasMany(Participant::class);
    }

    public function properties()
    {
        return $this->hasMany(EventProperty::class);
    }

    public function links()
    {
        return $this->hasMany(Link::class);
    }

    public function dates()
    {
        return $this->hasMany(EventDate::class);
    }

    public function roles()
    {
        return $this->hasMany(EventModeratorRole::class);
    }

    public function moderators()
    {
        return $this->hasMany(EventModerator::class);
    }

    public function posts()
    {
        return $this
            ->hasMany(Post::class)
            ->orderBy("created_at", "desc");
    }

    public function groups()
    {
        return $this->hasMany(Group::class)->where('private', false);
    }

    public function mapPools()
    {
        return $this->hasMany(MapPool::class);
    }

    public function gameServers()
    {
        return $this->hasMany(GameServer::class);
    }

    public function faq()
    {
        return $this->hasMany(EventFaq::class);
    }

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function formats()
    {
        return $this->hasMany(Format::class);
    }

    public function getFeaturedStreamAttribute()
    {
        $groups = collect($this->groups);

        $featuredMatch = $groups->transform(function (Group $group) {
            return $group->matches
                ->where("featuredStream", "!=", null)
                ->where("status_id", MatchModel::STATUS_LIVE);
        });

        if (!$featuredMatch) return [];
        if (!$featuredMatch->first()) return [];
        if ($featuredMatch->first()->isEmpty()) return [];

        return $featuredMatch->first()->first()->featuredStream;
    }

    public function getFeaturedMatchesAttribute()
    {
        $groupIds = collect($this->groups)->transform(function($group) {
           return $group->id;
        });

        $groupIds = $groupIds->toArray();

        return MatchModel::whereIn("group_id", $groupIds)
            ->where("status_id", MatchModel::STATUS_LIVE)
            ->orderBy("updated_at", "desc")
            ->limit(3)
            ->with(MatchModel::RELATIONS)
            ->get();
    }
}
