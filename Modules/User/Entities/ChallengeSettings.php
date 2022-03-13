<?php


namespace Modules\User\Entities;


/**
 * Class ChallengeSettings
 * @package Modules\User\Entities
 *
 * @property int $mappackId
 * @property int $mapId
 * @property int $coins
 * @property int $status
 * @property int $matchId
 */
class ChallengeSettings
{
    const PENDING  = 0;
    const ACCEPTED = 1;
    const REJECTED = 2;
    const LIVE     = 3;
    const ENDED    = 4;
}
