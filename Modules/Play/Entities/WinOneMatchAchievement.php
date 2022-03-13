<?php


namespace Modules\Play\Entities;


class WinOneMatchAchievement implements Achievement
{
    static public function getType()
    {
        return Achievement::TYPE_DAILY;
    }

    static public function getId()
    {
        return 1;
    }

    static public function getDefaultTarget()
    {
        return 1;
    }

    static public function getReward()
    {
        return 100;
    }
}
