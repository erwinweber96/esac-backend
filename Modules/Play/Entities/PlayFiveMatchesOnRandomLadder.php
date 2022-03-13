<?php

namespace Modules\Play\Entities;

class PlayFiveMatchesOnRandomLadder implements Achievement
{
    const BADGE_ID = 1131;

    static public function getType()
    {
        return Achievement::TYPE_DAILY;
    }

    static public function getId()
    {
        return 5;
    }

    static public function getDefaultTarget()
    {
        return 5;
    }

    static public function getReward()
    {
        return 1000;
    }
}
