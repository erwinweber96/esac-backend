<?php


namespace Modules\Page\Entities;


use App\Model\Model;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Modules\Console\Entities\ApiToken;
use Modules\Event\Entities\Event;
use Modules\Event\Entities\Participant;
use Modules\Link\Entities\Link;
use Modules\Post\Entities\Post;
use Modules\User\Entities\User;

/**
 * Class Page
 * @package Modules\Page\Entities
 *
 * Contains:
 * @property int        $id
 * @property string     $name
 * @property string     $slug
 * @property string     $about
 * @property Carbon     $createdAt
 * @property Carbon     $updatedAt
 * @property boolean    $private
 * @property string     $inviteToken
 * @property int        $elo
 *
 * Has:
 * @property PageMemberRole[]|Collection   $roles                      Roles inside ESAC. (f.e access to game servers)
 * @property Link[]|Collection             $links
 * @property PageMember[]|Collection       $members
 * @property Post[]|Collection             $posts
 * @property Event[]|Collection            $events
 * @property PageProperty[]|Collection     $properties
 * @property PageType                      $type
 * @property array                         $currentParticipation
 * @property ApiToken                      $apiToken
 * @property TeamEloHistory[]              $teamEloHistory
 * @property Participant[]|Collection      $participants
 *
 * Belongs to:
 * @property User $user
 */
class Page extends Model
{
    const TABLE_NAME  = "pages";
    const DEFAULT_ELO = 500;

    public function getTableName(): string
    {
       return self::TABLE_NAME;
    }

    protected $fillable = [
        'name',
        'slug',
        'about',
        'type_id',
        'user_id',
        'invite_token',
        'private'
    ];

    public $relations = [
        'links',
        'posts',
        'properties',
        'type',
        'user',
        'members',
        'members.user',
        'members.roles',
        'participants',
        'participants.groupResults',
        'participants.groupResults.group',
    ];

    public $hidden = [
        'type_id',
        'invite_token'
    ];

//    public $appends = [
////        "currentParticipation"
//    ];

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->string("name");
        $table->string("slug");
        $table->text("about");
        $table->timestamps();
        $table->integer("type_id")->index();
        $table->integer("user_id")->index();
        $table->string("invite_token")->nullable()->unique()->index();
    }

    public function roles()
    {
        return $this->hasMany(PageMemberRole::class);
    }

    public function links()
    {
        return $this->hasMany(Link::class);
    }

    public function members()
    {
        return $this->hasMany(PageMember::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class)->orderBy("created_at", "desc");
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function properties()
    {
        return $this->hasMany(PageProperty::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function type()
    {
        return $this->belongsTo(PageType::class);
    }

    public function getCurrentParticipationAttribute()
    {
        $participations = Participant::where("page_id", $this->id)->with("event")->get();

        if ($participations->isEmpty()) {
            return [];
        }

        $participations = $participations->filter(function ($participation) {
            return $participation->event->statusId == Event::STATUS_LIVE &&
                $participation->event->private == false;
        });

        return $participations;
    }

    public function apiToken()
    {
        return $this->hasOne(ApiToken::class);
    }

    public function teamEloHistory()
    {
        return $this->hasMany(TeamEloHistory::class);
    }

    public function participants()
    {
        return $this->hasMany(Participant::class);
    }
}
