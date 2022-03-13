<?php


namespace Modules\Page\Policies;


use Modules\Page\Entities\Page;
use Modules\Page\Entities\PageMember;
use Modules\Page\Entities\PageMemberRole;
use Modules\User\Entities\User;

/**
 * Class PagePolicy
 * @package Modules\Page\Policies
 *
 * Manages page member roles.
 */
class PagePolicy
{
    /** @var PageMember $member */
    private $member;

    public function before(User $user, $ability, Page $page)
    {
        $member = PageMember::where("user_id", $user->id)
            ->where("page_id", $page->id)
            ->first();

        if (!$member) {
            return false;
        }

        $this->member = $member;
    }

    public function invite_members()
    {
        $allowed = PageMemberRole::where("name", PageMemberRole::INVITE_MEMBERS)
            ->where("member_id", $this->member->id)
            ->first();

        return (bool)$allowed;
    }

    public function create_events()
    {
        $allowed = PageMemberRole::where("name", PageMemberRole::CREATE_EVENTS)
            ->where("member_id", $this->member->id)
            ->first();

        return (bool)$allowed;
    }

    public function create_posts()
    {
        $allowed = PageMemberRole::where("name", PageMemberRole::CREATE_POSTS)
            ->where("member_id", $this->member->id)
            ->first();

        return (bool)$allowed;
    }

    public function see_private_events()
    {
        $allowed = PageMemberRole::where("name", PageMemberRole::SEE_PRIVATE_EVENTS)
            ->where("member_id", $this->member->id)
            ->first();

        return (bool)$allowed;
    }
}
