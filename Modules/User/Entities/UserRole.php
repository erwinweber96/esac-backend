<?php


namespace Modules\User\Entities;


use App\Model\Model;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class UserRole
 * @package Modules\User\Entities
 *
 * @property int    $id
 * @property User   $user
 * @property string $role
 */
class UserRole extends Model
{
    const TABLE_NAME = "user_roles";

    const CREATE_PAGE = "create_page";

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->integer("user_id")->index();
        $table->string("role");
        $table->timestamps();
    }

    protected $fillable = [
        "user_id",
        "role"
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
