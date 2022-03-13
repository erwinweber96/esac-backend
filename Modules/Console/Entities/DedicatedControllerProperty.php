<?php


namespace Modules\Console\Entities;

use App\Model\Model;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class DedicatedControllerProperty
 * @package Modules\Console\Entities
 *
 * @property int    $id
 * @property int    $port
 * @property string $key
 * @property string $value
 */
class DedicatedControllerProperty extends Model
{
    const TABLE_NAME = "dedicated_controller_properties";

    const CLUB_NAME = "club_name";
    const ROOM_NAME = "room_name";

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->integer("port");
        $table->string("key");
        $table->string("value");
        $table->timestamps();
    }

    public $fillable = [
        "id",
        "port",
        "key",
        "value",
        "created_at",
        "updated_at"
    ];
}
