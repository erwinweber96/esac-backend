<?php


namespace Modules\User\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Maniaplanet\OAuth2\Client\Maniaplanet\Provider\Maniaplanet;
use Modules\User\Entities\MpAuth;
use Modules\User\Entities\User;
use Modules\User\Exceptions\CouldNotConnectManiaPlanet;
use Modules\User\Exceptions\ManiaPlanetLoginTaken;
use Sentry\Laravel\Facade;
use Tymon\JWTAuth\JWTAuth;

/**
 * Class MpAuthController
 * @package Modules\User\Http\Controllers
 */
class MpAuthController
{
    public function linkManiaPlanetToUser()
    {
        $maniaPlanetProvider = new Maniaplanet([
            'clientId'                => MpAuth::CLIENT_ID,
            'clientSecret'            => MpAuth::CLIENT_SECRET,
            'redirectUri'             => MpAuth::REDIRECT_URI,
            'responseType'            => MpAuth::RESPONSE_TYPE
        ]);
        $authorizationUrl = $maniaPlanetProvider->getAuthorizationUrl(
            [
                'scope' => 'basic'
            ]
        );
        return $authorizationUrl;
    }

    public function oAuthResponse(Request $request)
    {
        /** @var Facade $sentry */
        $sentry = app('sentry');

        $code   = $request->input("code");
        $state  = $request->input("state");

        $auth = auth()->user();

        try {
            $res = $this->oAuthResponded($code, $state, $auth->id);
        } catch (ManiaPlanetLoginTaken $exception) {
            $sentry->captureException($exception);
            return response()->json(["error" => "Login already linked to another account."],
                Response::HTTP_BAD_REQUEST);
        } catch (CouldNotConnectManiaPlanet $exception) {
            $sentry->captureException($exception);
            return response()->json(["error" => "Something went wrong."], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Throwable $exception) {
            $sentry->captureException($exception);
            return response()->json(["error" => "Something went wrong."], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $res;
    }

    /**
     * @param $code
     * @param $state
     * @param $userId
     * @return \Illuminate\Http\JsonResponse
     * @throws CouldNotConnectManiaPlanet
     * @throws ManiaPlanetLoginTaken
     */
    private function oAuthResponded($code, $state, $userId)
    {
        $maniaPlanetProvider = new Maniaplanet([
            'clientId'                => MpAuth::CLIENT_ID,
            'clientSecret'            => MpAuth::CLIENT_SECRET,
            'redirectUri'             => MpAuth::REDIRECT_URI,
            'responseType'            => MpAuth::RESPONSE_TYPE
        ]);

        try {
            $accessToken = $maniaPlanetProvider->getAccessToken('authorization_code', [
                'code' => $code
            ]);
        } catch (\Throwable $exception) {
            /** @var Facade $sentry */
            $sentry = app('sentry');
            $sentry->captureException($exception);
        }

        try {
            $request = $maniaPlanetProvider->getAuthenticatedRequest(
                'GET',
                'https://prod.live.maniaplanet.com/webservices/me',
                $accessToken
            );
        } catch (\Throwable $exception) {
            /** @var Facade $sentry */
            $sentry = app('sentry');
            $sentry->captureException($exception);
        }

        try {
            $response = $maniaPlanetProvider->getHttpClient()->send($request);
        } catch (\Throwable $exception) {
            /** @var Facade $sentry */
            $sentry = app('sentry');
            $sentry->captureException($exception);
        }

        $user = json_decode($response->getBody()->getContents());
        $login = $user->login;

        $loginTaken = MpAuth::where("login", $login)->get()->count();

        if ($loginTaken) {
            throw new ManiaPlanetLoginTaken();
        }

        try {
            MpAuth::create([
                "login" => $login,
                "user_id" => $userId
            ]);
        } catch (\Throwable $exception) {
            /** @var Facade $sentry */
            $sentry = app('sentry');
            $sentry->captureException($exception);

            throw new CouldNotConnectManiaPlanet();
        }

        return response()->json([
            "message" => "Success"
        ]);
    }
}
