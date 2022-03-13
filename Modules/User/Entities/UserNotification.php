<?php


namespace Modules\User\Entities;


use App\Model\Model;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class UserNotification
 * @package Modules\User\Entities
 *
 * @property integer $id
 * @property integer $userId
 * @property string  $title
 * @property string  $message
 * @property string  $variant
 * @property bool    $read
 * @property string  $url
 *
 * @property Carbon  $updatedAt
 * @property Carbon  $createdAt
 *
 * @property User    $user
 */
class UserNotification extends Model
{
    const TABLE_NAME = "user_notifications";

    const PRIMARY_VARIANT   = "primary";
    const DANGER_VARIANT    = "danger";
    const INFO_VARIANT      = "info";
    const WARNING_VARIANT   = "warning";
    const SECONDARY_VARIANT = "secondary";

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    protected $fillable = [
        "id",
        "user_id",
        "title",
        "message",
        "variant",
        "created_at",
        "updated_at",
        "read",
        "url"
    ];

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->integer("user_id")->index();
        $table->string("title");
        $table->string("message");
        $table->string("variant");
        $table->boolean("read")->default(false);
        $table->string("url")->nullable();

        $table->timestamps();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
