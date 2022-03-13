<?php


namespace Modules\Match\Entities;


use App\Model\Model;
use Illuminate\Database\Schema\Blueprint;
use Modules\Group\Entities\Format;

/**
 * Class LossCondition
 * @package Modules\Match\Entities
 *
 * Contains:
 * @property int $id
 * @property string $name
 * @property string $about
 *
 * Has:
 *
 *
 * Belongs to:
 * @property Format $format
 */
class LossCondition extends Model
{
    const TABLE_NAME = "loss_conditions";

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->string("name");
        $table->text("about");
        $table->integer("format_id")->index();
    }

    public function format()
    {
        return $this->belongsTo(Format::class);
    }
}
