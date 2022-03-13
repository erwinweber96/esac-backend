<?php


namespace Modules\Page\Entities;


use App\Model\Model;
use Illuminate\Database\Schema\Blueprint;
use Modules\User\Entities\User;

/**
 * Class PageMember
 * @package Modules\Page\Entities
 *
 * Contains:
 * @property int    $id
 * @property int    $typeId
 * @property int    $userId
 *
 * Has:
 * @property PageMemberRole[]   $roles
 * @property User               $user
 *
 * Belongs to:
 * @property Page $page
 */
class PageMember extends Model
{
    const TABLE_NAME = "page_members";

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->timestamps();
        $table->integer("user_id")->index();
        $table->integer("page_id")->index();
        $table->integer("type_id")->default(PageMemberType::MEMBER);
    }

    public $fillable = [
        "user_id",
        "page_id",
        "type_id"
    ];

    public $appends = [
        "type"
    ];

    public function roles()
    {
        return $this->hasMany(PageMemberRole::class, "member_id");
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function getTypeAttribute()
    {
        return PageMemberType::TYPES[$this->typeId];
    }
}
