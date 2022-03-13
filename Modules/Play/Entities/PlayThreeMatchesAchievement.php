<?php

namespace Modules\Play\Entities;

class PlayThreeMatchesAchievement implements Achievement
{

    static public function getType()
    {
        return Achievement::TYPE_DAILY;
    }

    static public function getId()
    {
        return 2;
    }

    static public function getDefaultTarget()
    {
        return 3;
    }

    static public function getReward()
    {
        return 50;
    }
}
