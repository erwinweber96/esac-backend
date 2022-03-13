<?php


namespace Modules\Link\Entities;


use App\Model\Model;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class LinkType
 * @package Modules\Link\Entities
 *
 * Contains:
 * @property int $id
 * @property string $name
 * @property string $value
 * @property string $iconClass
 *
 * Has:
 *
 *
 * Belongs to:
 *
 */
class LinkType extends Model
{
    const TABLE_NAME = "link_types";

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->string("name");
        $table->string("value");
        $table->string("icon_class");
    }
}
