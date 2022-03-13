<?php


namespace Modules\Console\Entities;


use App\Model\Model;
use Illuminate\Database\Schema\Blueprint;

/**
 * @property int    $id
 * @property string $message
 * @property string $createdAt
 * @property string $updatedAt
 */
class GlobalAlert extends Model
{
    const TABLE_NAME = "global_alerts";

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->string("message");
        $table->timestamps();
    }
}
