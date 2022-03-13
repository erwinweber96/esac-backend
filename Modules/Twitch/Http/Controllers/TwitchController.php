<?php

namespace Modules\Twitch\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Play\Console\CreateDailyMatchmakingLadders;
use Modules\Twitch\Entities\TwitchAccessToken;
use Modules\Twitch\Services\TwitchService;

/**
 * Class TwitchController
 * @package Modules\Twitch\Http\Controllers
 */
class TwitchController extends Controller
{
    /** @var TwitchService $service */
    private $service;

    /**
     * TwitchController constructor.
     * @param TwitchService $service
     */
    public function __construct(TwitchService $service)
    {
        $this->service = $service;
    }

    public function save(Request $request)
    {
        $code = $request->input("code");
        $auth = $this->service->getAuthByCode($code);

        $twitchToken = new TwitchAccessToken();

        $twitchToken->userId = auth()->user()->id;
        $twitchToken->accessToken = $auth['access_token'];
        $twitchToken->refreshToken = $auth['refresh_token'];

        $twitchToken->save();

        return ["message" => "success"];
    }

    public function unlink()
    {
        try {
            TwitchAccessToken::where("user_id", auth()->user()->id)->delete();
        } catch (\Throwable $exception) {
            return response()->json([
                "error" => [
                    "messages" => ["Could not unlink twitch."]
                ]
            ]);
        }

        return ["message" => "success"];
    }

    public function getUser()
    {
        return $this->service->getTwitchUser(auth()->user()->id);
    }

    public function test()
    {
//        return $this->service->getStreamInfo(15);
        /** @var CreateDailyMatchmakingLadders $test */
        $test = app(CreateDailyMatchmakingLadders::class);
        $test->handle();
    }
}
