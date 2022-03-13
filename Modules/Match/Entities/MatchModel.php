<?php


namespace Modules\Match\Entities;

use App\Model\Model;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Modules\Event\Entities\Participant;
use Modules\GameServer\Entities\GameServer;
use Modules\Group\Entities\Format;
use Modules\Group\Entities\FormatType;
use Modules\Group\Entities\Group;
use Modules\Map\Entities\MapPool;

/**
 * Class Match
 * @package Modules\Match\Entities
 *
 * Contains:
 * @property int    $id
 * @property string $name
 * @property Carbon $createdAt
 * @property Carbon $updatedAt
 * @property Carbon $date
 * @property int    $statusId
 * @property int    $mapPoolId
 * @property int    $groupId
 *
 * Has:
 * @property Participant[]|Collection      $participants
 * @property Format[]|Collection           $formats
 * @property MatchResult[]|Collection      $results
 * @property GameServer[]|Collection       $gameServers
 * @property LiveStream[]|Collection       $streams
 * @property MatchProperty[]|Collection    $properties
 * @property MatchComment[]|Collection     $comments
 * @property Vod[]|Collection              $vods
 * @property MatchAlert[]|Collection       $matchAlerts
 *
 * @property MapPool            $mapPool
 * @property array              $totalMatchResults
 * @property GameServer         $selectedGameServer
 * @property LiveStream         $featuredStream
 *
 * Belongs to:
 * @property Group $group
 */
class MatchModel extends Model
{
    const TABLE_NAME = "matches";

    const STATUS_UPCOMING   = 1;
    const STATUS_LIVE       = 2;
    const STATUS_ENDED      = 3;

    const ALL_STATUS = [
        self::STATUS_UPCOMING => "Upcoming",
        self::STATUS_LIVE => "Live",
        self::STATUS_ENDED => "Ended"
    ];

    protected $table = self::TABLE_NAME;

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->string("name");
        $table->timestamps();
        $table->timestamp("date")->nullable();
        $table->integer("map_pool_id")->index();
        $table->integer("group_id")->index();
        $table->integer("status_id")->default(self::STATUS_UPCOMING);
    }

    public $fillable = [
        'name',
        'group_id',
        'date',
        'map_pool_id',
        'status_id'
    ];

    protected $appends = [
        'totalMatchResults',
        'selectedGameServer',
        'featuredStream',
        'status'
    ];

    public $relations = self::RELATIONS;

    const RELATIONS = [
        'mapPool',
        'group',
        'participants',
        'group.participants',
        'participants.user',
        'participants.page',
        'participants.page.user',
        'group.participants.user',
        'group.participants.page',
        'group.formats',
        'formats',
        'results',
        'results.participant',
        'results.participant.user',
        'results.participant.page',
        'gameServers',
        'liveStreams',
        'liveStreams.link',
        'vods',
        'vods.link'
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function participants()
    {
        return $this->belongsToMany(Participant::class, 'match_participant', 'match_id');
    }

    public function formats()
    {
        return $this->belongsToMany(Format::class, 'format_match', 'match_id');
    }

    public function results()
    {
        return $this
            ->hasMany(MatchResult::class, 'match_id')
            ->orderBy("result", "desc");
    }

    public function mapPool()
    {
        return $this->belongsTo(MapPool::class);
    }

    public function gameServers()
    {
        return $this->belongsToMany(GameServer::class, 'game_server_match', 'match_id');
    }

    public function liveStreams()
    {
        return $this->hasMany(LiveStream::class, 'match_id');
    }

    public function properties()
    {
        return $this->hasMany(MatchProperty::class, 'match_id');
    }

    public function comments()
    {
        return $this->hasMany(MatchComment::class, 'match_id');
    }

    public function vods()
    {
        return $this->hasMany(Vod::class, 'match_id');
    }

    public function getTotalMatchResultsAttribute()
    {
        $results      = collect($this->results);
        $totalResults = null;

        $timeAttack = false;
        if (count($this->formats)) {
            if ($this->formats[0]->typeId == FormatType::TIME_ATTACK_VALUE) {
                $timeAttack = true;
            }
        }

        foreach ($this->participants as $participant) {
            $totalResults[$participant->id] = $results
                ->where("participant_id", $participant->id)
                ->where("pending", false)
                ->where("map_id", null);

            if ($timeAttack) {
                $totalResults[$participant->id] = $totalResults[$participant->id]->sortBy("result");
            } else {
                $totalResults[$participant->id] = $totalResults[$participant->id]->sortByDesc("created_at");
            }

            $totalResults[$participant->id] = $totalResults[$participant->id]->first();
        }

        return $totalResults;
    }

    public function getSelectedGameServerAttribute()
    {
        return $this->gameServers()
            ->where("pending", false)
            ->orderBy("updated_at", "desc")
            ->first();
    }

    public function getFeaturedStreamAttribute()
    {
        $liveStreams = $this->liveStreams()
            ->with('link')
            ->orderBy("created_at", "desc")
            ->get();

        /** @var LiveStream $liveStream */
        foreach($liveStreams as $liveStream) {
            if (!$liveStream->link->pending) {
                return $liveStream;
            }
        }

        return null;
    }

    public function getStatusAttribute()
    {
        $match = $this->where("id", $this->id)->get('status_id');
        $statusId = $match->first()->status_id;
        return self::ALL_STATUS[$statusId];
    }

    public function matchAlerts()
    {
        return $this->hasMany(MatchAlert::class, 'match_id');
    }
}
