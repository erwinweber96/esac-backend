<?php


namespace App\Services;

/**
 * Class PseudoCrypt
 * @package App\Services
 */
class PseudoCrypt
{
    private const KEY = "8712g3eiuyjbkwqndc";

    public static function hash($code)
    {
        return md5($code.self::KEY);
    }
}
