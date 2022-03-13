<?php


namespace Modules\Group\Entities;


use App\Model\Model;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class FormatProperty
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
 * Belongs to:
 * @property Format $format
 */
class FormatProperty extends Model
{
    const TABLE_NAME = "format_properties";

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
        $table->integer("format_id")->index();
    }

    public function format()
    {
        return $this->belongsTo(Format::class);
    }
}
