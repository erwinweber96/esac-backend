<?php


namespace Modules\Group\Entities;


use App\Model\Model;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class FormatType
 * @package Modules\Group\Entities
 *
 * Contains:
 * @property int    $id
 * @property string $text
 * @property string $value
 *
 * Has:
 * @property Format[] $formats
 *
 * Belongs to:
 *
 */
class FormatType extends Model
{
    const OTHER_NAME          = "Other";
    const TIME_ATTACK_NAME    = "Time Attack";
    const ROUNDS_NAME         = "Rounds";
    const CUP_NAME            = "Cup";
    const LAPS_NAME           = "Laps";
    const TEAM_NAME           = "Teams";

    const OTHER_VALUE         = 0;
    const TIME_ATTACK_VALUE   = 1;
    const ROUNDS_VALUE        = 2;
    const CUP_VALUE           = 3;
    const LAPS_VALUE          = 4;
    const TEAM_VALUE          = 5;

    const VALUES = [
        self::OTHER_VALUE,
        self::TIME_ATTACK_VALUE,
        self::ROUNDS_VALUE,
        self::CUP_VALUE,
        self::LAPS_VALUE,
        self::TEAM_VALUE,
    ];

    const NAMES = [
        self::OTHER_NAME,
        self::TIME_ATTACK_NAME,
        self::ROUNDS_NAME,
        self::CUP_NAME,
        self::LAPS_NAME,
        self::TEAM_NAME,
    ];

    const TABLE_NAME = "format_types";

    protected $fillable = [
        'id',
        'text',
        'value'
    ];

    public $timestamps = false;

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->string("text");
        $table->string("value");
    }

    public function formats()
    {
        return $this->hasMany(Format::class);
    }
}
