<?php


namespace Modules\User\Entities;


use App\Model\Model;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class MpAuth
 * @package Modules\User\Entities
 *
 * @property int    $id
 * @property string $login
 * @property Carbon $updatedAt
 * @property Carbon $createdAd
 *
 * @property User   $user
 */
class MpAuth extends Model
{
    const TABLE_NAME = "mp_auths";

    const CLIENT_ID = "xxxxxxxxxxxxxxxxxxx"; //TODO:
    const CLIENT_SECRET = "xxxxxxxxxxxxxxxxxxxxxxxxxxx"; //TODO:
    const REDIRECT_URI = "https://esac.gg/user/oauth/maniaplanet";
    const RESPONSE_TYPE = "code";

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->integer('user_id')->unique();
        $table->string('login')->unique();
        $table->timestamps();
    }

    protected $fillable = [
        "login",
        "user_id"
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
