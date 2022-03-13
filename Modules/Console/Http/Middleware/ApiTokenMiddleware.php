<?php


namespace Modules\Console\Http\Middleware;


use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Modules\Console\Entities\ApiToken;
use Modules\User\Entities\User;

/**
 * Class ApiTokenMiddleware
 * @package Modules\Console\Http\Middleware
 */
class ApiTokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        $apiToken = $request->input("token");

        if (!$apiToken) {
            $requestArray = $request->toArray();
            if (!$apiToken = $requestArray['token']) {
                return response()->json([
                    "error" => "API Token is missing."
                ], Response::HTTP_UNAUTHORIZED);
            }
        }

        /** @var ApiToken $object */
        $object = ApiToken::where("token", $apiToken)->first();

        if (!$object) {
            return response()->json([
                "error" => "Invalid API Token."
            ], Response::HTTP_UNAUTHORIZED);
        }

        /** @var User $user */
        $user = User::where("id", $object->userId)->first();

        if (!$user) {
            return response()->json([
                "error" => "Invalid API Token. User not found."
            ], Response::HTTP_UNAUTHORIZED);
        }

        Auth::login($user);

        return $next($request);
    }
}
