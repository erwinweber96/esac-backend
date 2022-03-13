<?php


namespace Modules\Play\Entities;


interface Achievement
{
    const TYPE_DAILY = "daily";

    static public function getType();
    static public function getId();
    static public function getDefaultTarget();
    static public function getReward();
}
