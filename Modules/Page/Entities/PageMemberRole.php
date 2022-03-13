<?php


namespace Modules\Page\Entities;


use App\Model\Model;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class PageRole
 * @package Modules\Page\Entities
 *
 * Contains:
 * @property int    $id
 * @property string $name
 *
 * Has:
 *
 *
 * Belongs to:
 * @property PageMember     $member
 * @property Page           $page
 */
class PageMemberRole extends Model
{
    const TABLE_NAME = "page_member_roles";

    const INVITE_MEMBERS     = "invite_members";
    const CREATE_EVENTS      = "create_events";
    const CREATE_POSTS       = "create_posts";
    const SEE_PRIVATE_EVENTS = "see_private_events";

    const ROLES = [
        self::INVITE_MEMBERS,
        self::CREATE_POSTS,
        self::CREATE_EVENTS,
        self::SEE_PRIVATE_EVENTS
    ];

    public $fillable = [
        "name",
        "member_id",
        "page_id"
    ];

    public $timestamps = false;

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
       $table->id();
       $table->string("name");
       $table->integer("member_id")->index();
       $table->integer("page_id")->index();
    }

    public function member()
    {
        $this->belongsTo(PageMember::class, "member_id");
    }

    public function page()
    {
        $this->belongsTo(Page::class);
    }
}
