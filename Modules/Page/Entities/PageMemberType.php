<?php


namespace Modules\Page\Entities;


class PageMemberType
{
    const INACTIVE  = -1;
    const MEMBER    = 0;
    const PLAYER    = 1;
    const MODERATOR = 2;
    const CREATOR   = 3;

    const TYPES = [
        "Member",
        "Player",
        "Moderator",
        "Creator",
        "Inactive"
    ];
}
