<?php


namespace Modules\Match\Entities;


use App\Model\Model;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class MatchResultProperty
 * @package Modules\Group\Entities
 *
 * Contains:
 * @property int    $id
 * @property string $key
 * @property string $value
 * @property bool   $readOnly
 *
 * Has:
 *
 *
 *
 * Belongs to:
 * @property MatchResult $matchResult
 */
class MatchResultProperty extends Model
{
    const TABLE_NAME = "match_result_properties";

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->string("key");
        $table->string("value");
        $table->boolean("read_only");
        $table->integer("match_result")->index();
    }

    public function matchResult()
    {
        return $this->belongsTo(MatchResult::class);
    }
}
