<?php


namespace Modules\Play\Entities;


class StreamWhileWinningAchievement implements Achievement
{
    const BADGE_ID = 1130;

    static public function getType()
    {
        return Achievement::TYPE_DAILY;
    }

    static public function getId()
    {
        return 4;
    }

    static public function getDefaultTarget()
    {
        return 1;
    }

    static public function getReward()
    {
        return 500;
    }
}
