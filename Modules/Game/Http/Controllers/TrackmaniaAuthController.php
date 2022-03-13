<?php


namespace Modules\Game\Http\Controllers;


use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Modules\Game\Entities\TrackmaniaAccessToken;
use Modules\Game\Entities\TrackmaniaAuth;
use Modules\User\Entities\User;

/**
 * Class TrackmaniaAuthController
 * @package Modules\Game\Http\Controllers
 */
class TrackmaniaAuthController
{
    /** @var Client $client */
    private $client;

    /**
     * TrackmaniaAuthController constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }


    /**
     * @param Request $request
     * @return string[]
     */
    public function save(Request $request)
    {
        $code = $request->input("code");

        $response = $this->client->request(
            'POST',
            "https://api.trackmania.com/api/access_token",
            [
                'form_params' => [
                    "client_id" => "xxxxxxxxxxxxxxxxxxxx", //TODO:
                    "client_secret" => "xxxxxxxxxxxxxxxxxxxxxx", //TODO:
                    "code" => $code,
                    "redirect_uri" => "https://esac.gg/tm_oauth",
                    "grant_type" => "authorization_code"
                ],
                "headers" => [
                    "Content-Type" => "application/x-www-form-urlencoded"
                ]
            ]
        );

        $data = json_decode($response->getBody()->getContents());
        $accessToken = $data->access_token;

        $response = $this->client->request(
            'GET',
            "https://api.trackmania.com/api/user",
            [
                'headers' =>
                    [
                        "Authorization" => "Bearer ".$accessToken,
                    ]
            ]
        );

        $userData = json_decode($response->getBody()->getContents());
        $accountId = $userData->account_id;
        $displayName = $userData->display_name;

        /** @var User $user */
        $user = auth()->user();

        $trackmaniaAuth = new TrackmaniaAuth();

        $trackmaniaAuth->userId = $user->id;
        $trackmaniaAuth->accountId = $accountId;
        $trackmaniaAuth->displayName = $displayName;

        $user->tmNickname = $displayName;
        $user->save();

        $trackmaniaAuth->save();

        return ["message" => "success"];
    }
}
