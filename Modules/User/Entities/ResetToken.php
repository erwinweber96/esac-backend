<?php


namespace Modules\User\Entities;


use App\Model\Model;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class ResetToken
 * @package Modules\User\Entities
 *
 * @property int    $id
 * @property string $email
 * @property string $token
 * @property Carbon $expires
 * @property Carbon $updatedAt
 * @property Carbon $createdAt
 */
class ResetToken extends Model
{
    const TABLE_NAME = "reset_tokens";

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->string("email");
        $table->string("token");
        $table->timestamp("expires");
        $table->timestamps();
    }
}
