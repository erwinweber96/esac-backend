<?php


namespace Modules\User\Entities;


use App\Model\Model;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class CoinTransaction
 * @package Modules\User\Entities
 *
 * @property int    $id
 * @property int    $userId
 * @property float  $amount
 * @property string $description
 * @property Carbon $updatedAt
 * @property Carbon $createdAt
 */
class CoinTransaction extends Model
{
    const TABLE_NAME = "coin_transactions";

    protected $fillable = [
        "user_id",
        "amount",
        "description",
        "updated_at",
        "created_at"
    ];

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->integer("user_id");
        $table->float("amount");
        $table->string("description");
        $table->timestamps();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
