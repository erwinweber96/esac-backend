<?php


namespace Modules\Console\Traits;


use Illuminate\Http\Response;
use Modules\Console\Entities\ConsoleAccess;
use Modules\User\Entities\User;

/**
 * Trait RequiresConsoleAccess
 * @package Modules\Console\Traits
 */
trait RequiresConsoleAccess
{
    public function verifyConsoleAccess()
    {
        /** @var User $user */
        $user = auth()->user();

        $access = ConsoleAccess::where("user_id", $user->id)->get();

        /** @var ConsoleAccess $pass */
        foreach ($access as $pass) {
            if (!$pass->until->isPast()) {
                return $pass;
            }
        }

        throw new \Exception("Not Authorized", Response::HTTP_UNAUTHORIZED);
    }
}
