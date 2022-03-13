<?php

namespace Modules\User\Entities;

use App\Model\AuthenticatableModel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Modules\Game\Entities\TrackmaniaAccessToken;
use Modules\Game\Entities\TrackmaniaAuth;
use Modules\Market\Entities\Badge;
use Modules\Event\Entities\Participant;
use Modules\Match\Entities\MatchComment;
use Modules\Page\Entities\Page;
use Modules\Play\Entities\CaseDrop;
use Modules\Post\Entities\Post;
use Modules\Twitch\Entities\TwitchAccessToken;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * Class User
 * @package Modules\User\Entities
 *
 * Contains:
 * @property integer    $id
 * @property string     $nickname
 * @property string     $email
 * @property string     $password
 * @property string     $firstName
 * @property string     $lastName
 * @property integer    $elo
 * @property integer    $coins
 * @property boolean    $admin
 * @property string     $queue
 * @property string     $nat
 * @property string     $referral
 * @property string     $badgeId
 * @property string     $tmNickname
 * @property string     $title
 *
 * Has:
 * @property Post[]|Collection          $posts
 * @property Page[]|Collection          $pages
 * @property MatchComment[]|Collection  $matchComments
 * @property Participant[]|Collection   $participants
 * @property UserRole[]|Collection      $roles
 * @property MpAuth|Collection          $maniaplanet
 * @property Badge[]|Collection         $badges
 * @property Discord                    $discord
 * @property FriendRequest[]|Collection $sentFriendRequests
 * @property FriendRequest[]|Collection $receivedFriendRequests
 * @property TwitchAccessToken          $twitchAccessToken
 */
class User extends AuthenticatableModel implements JWTSubject
{
    /**
     * The table name saved in the database
     */
    const TABLE_NAME = "users";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nickname',
        'email',
        'password',
        'first_name',
        'last_name',
        'elo',
        'coins',
        'nat',
        'referral',
        'badge_id',
        'tm_nickname'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email',
        'queue',
        'referral',
        'coins',
        'first_name',
        'last_name',
        'streamer',
        'content_creator',
        'manager',
        'organiser',
        'membership',
        'admin'
    ];

    public $relations = [
        'posts',
        'pages',
        'maniaplanet',
        'badges',
        "discord",
    ];

    /**
     * This should NOT be modified unless absolutely necessary.
     * If you want to modify the blueprint, consider creating a new migration.
     *
     * @param Blueprint $table
     *
     * @return void
     */
    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->increments('id');
        $table->string('nickname');
        $table->string('email')->unique();
        $table->string('password');
        $table->string('first_name')->default("");
        $table->string('last_name')->default("");
        $table->integer('elo')->default(500);
        $table->integer('coins')->default(0);
        $table->boolean('admin')->default(0);
        $table->string('queue')->default('quick');
        $table->string('nat', 2)->default("eu");
        $table->string('referral')->default("");
        $table->integer("badge_id")->default(0);
        $table->rememberToken();
        $table->timestamps();
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    /**
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function pages()
    {
        return $this->hasMany(Page::class);
    }

    public function matchComments()
    {
        return $this->hasMany(MatchComment::class);
    }

    public function participants()
    {
        return $this->hasMany(Participant::class);
    }

    public function roles()
    {
        return $this->hasMany(UserRole::class);
    }

    public function maniaplanet()
    {
        return $this->hasOne(MpAuth::class, "user_id");
    }

    public function badges()
    {
        return $this->belongsToMany(Badge::class);
    }

    public function discord()
    {
        return $this->hasOne(Discord::class);
    }

    public function twitchAccessToken()
    {
        return $this->hasOne(TwitchAccessToken::class);
    }

    public function trackmania()
    {
        return $this->hasOne(TrackmaniaAuth::class);
    }

    public function receivedFriendRequests()
    {
        return $this->hasMany(FriendRequest::class, "to_user_id");
    }

    public function caseDrops()
    {
        return $this->hasMany(CaseDrop::class);
    }

    public function sentFriendRequests()
    {
        return $this
            ->hasMany(FriendRequest::class, "from_user_id")
            ->where("status_id", "!=", FriendRequest::REJECTED);
    }

    /**
     * @return User[]|Collection
     */
    public function getFriends()
    {
        $acceptedRequests = FriendRequest::where(function($query) {
            $query->where("from_user_id", $this->id)->orWhere("to_user_id", $this->id);
        })->where("status_id", FriendRequest::ACCEPTED)->get();

        $friends = [];

        /** @var FriendRequest $friend */
        foreach ($acceptedRequests as $friend) {
            if ($friend->toUserId == $this->id) {
                $friends[] = $friend->fromUser()->first();
            } else {
                $friends[] = $friend->toUser()->first();
            }
        }

        return collect($friends);
    }

    public function getFriendsAttribute()
    {
        $acceptedRequests = FriendRequest::where(function($query) {
            $query->where("from_user_id", $this->id)->orWhere("to_user_id", $this->id);
        })->where("status_id", FriendRequest::ACCEPTED)->get();

        $friends = [];

        /** @var FriendRequest $friend */
        foreach ($acceptedRequests as $friend) {
            if ($friend->toUserId == $this->id) {
                $user = $friend->fromUser()->first([
                    "id",
                    "nickname",
                    "nat",
                    "badge_id",
                    "tm_nickname",
                    "elo"
                ]);
            } else {
                $user = $friend->toUser()->first([
                    "id",
                    "nickname",
                    "nat",
                    "badge_id",
                    "tm_nickname",
                    "elo"
                ]);
            }

            $friends[] = [
                "id" => $user->id,
                "nickname" => $user->nickname,
                "nat" => $user->nat,
                "badge_id" => $user->badge_id,
                "tm_nickname" => $user->tm_nickname,
                "elo" => $user->elo
            ];
        }

        return $friends;
    }

    public function coinTransactions()
    {
        return $this->hasMany(CoinTransaction::class);
    }
}
