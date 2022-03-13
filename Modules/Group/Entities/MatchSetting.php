<?php


namespace Modules\Group\Entities;


use App\Model\Model;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class MatchSetting
 * @package Modules\Group\Entities
 *
 * Contains:
 * @property int    $id
 * @property string $key
 * @property string $value
 * @property int    $formatId
 *
 * Has:
 *
 *
 * Belongs to:
 * @property Format $format
 */
class MatchSetting extends Model
{
    const TABLE_NAME = "match_settings";

    const KEYS = [
        "S_ChatTime"                    => "int",
        "S_UseClublinks"                => "bool",
        "S_UseClublinksSponsors"        => "bool",
        "S_NeutralEmblemUrl"            => "string",
        "S_IsChannelServer"             => "bool",
        "S_AllowRespawn"                => "bool",
        "S_RespawnBehaviour"            => "int",
        "S_HideOpponents"               => "bool",
        "S_PointsLimit"                 => "int",
        "S_FinishTimeout"               => "int",
        "S_UseAlternateRules"           => "bool",
        "S_ForceLapsNb"                 => "int",
        "S_DisplayTimeDiff"             => "bool",
        "S_PointsRepartition"           => "string",
        "S_RoundsPerMap"                => "int",
        "S_NbOfWinners"                 => "int",
        "S_WarmUpNb"                    => "int",
        "S_WarmUpDuration"              => "int",
        "S_TimeLimit"                   => "int",
        "S_DisableGiveUp"               => "bool",
        "S_MapsPerMatch"                => "int",
        "S_UseTieBreak"                 => "bool",
        "S_MaxPointsPerRound"           => "int",
        "S_PointsGap"                   => "int",
        "S_UseCustomPointsRepartition"  => "bool",
        "S_CumulatePoints"              => "bool"
    ];

    public $fillable = [
        "key",
        "value",
        "format_id"
    ];

    public $timestamps = false;

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->string("key");
        $table->string("value");
        $table->integer("format_id")->index();
    }

    public function format()
    {
        $this->belongsTo(Format::class);
    }
}
