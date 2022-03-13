<?php


namespace Modules\Play\Services;


/**
 * Class RankingService
 * @package Modules\Play\Services
 */
class RankingService
{
    const DIVIDER       = 400;
    const MULTIPLIER    = 10;

    /**
     * @param $playerRating
     * @param $opponentRating
     *
     * @return float|int
     */
    public function getProbabilityToWin($playerRating, $opponentRating)
    {
        $probability = 1 / (1 + pow(10, ($opponentRating - $playerRating) / self::DIVIDER));
        return round($probability, 2);
    }

    /**
     * @param $playerRating
     * @param $probabilityToWin
     * @param bool $playerHasWon
     * @param bool $isEqual
     *
     * @return float|int
     */
    public function getNewRating($playerRating, $probabilityToWin, bool $playerHasWon, bool $isEqual = false)
    {
        if ($isEqual) {
            return $playerRating;
        }

        $newRating = $playerRating + self::MULTIPLIER * ((int)$playerHasWon - $probabilityToWin);

        if (!$playerHasWon && $newRating > $playerRating) {
            return $playerRating - 1;
        }

        if ($playerRating == $newRating) {
            if ($playerHasWon) {
                return $playerRating + 1;
            } else {
                return $playerRating - 1;
            }
        }

        return intval($newRating);
    }
}
