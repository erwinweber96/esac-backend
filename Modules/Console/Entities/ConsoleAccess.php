<?php


namespace Modules\Console\Entities;


use App\Model\Model;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Modules\User\Entities\User;

/**
 * Class ConsoleAccess
 * @package Modules\Console\Entities
 *
 * @property integer $id
 * @property integer $userId
 * @property Carbon  $from
 * @property Carbon  $until
 * @property string  $description
 * @property Carbon  $updatedAt
 * @property Carbon  $createdAt
 *
 * @property User    $user
 */
class ConsoleAccess extends Model
{
    const TABLE_NAME = "console_access";

    public $table = self::TABLE_NAME;

    protected $dates = ['created_at', 'updated_at', 'from', 'until'];

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->integer("user_id")->index();
        $table->timestamp("from")->nullable();
        $table->timestamp("until")->nullable();
        $table->string("description");
        $table->timestamps();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
