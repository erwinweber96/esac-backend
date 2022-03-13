<?php


namespace Modules\Console\Entities;


use App\Model\Model;
use Illuminate\Database\Schema\Blueprint;
use Modules\Page\Entities\Page;
use Modules\User\Entities\User;

/**
 * Class ApiToken
 * @package Modules\Console\Entities
 *
 * @property integer $id
 * @property string  $token
 *
 * @property Page $page
 * @property User $user
 */
class ApiToken extends Model
{
    const TABLE_NAME = "api_tokens";

    public $relations = [
        "page"
    ];

    public $fillable = [
        "user_id",
        "page_id",
        "token"
    ];

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->integer("user_id");
        $table->integer("page_id");
        $table->string("token")->index();
        $table->timestamps();
    }

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
