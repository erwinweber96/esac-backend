<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('console')->group(function () {
    Route::post("/manual_rank_calc/{matchId}", "ConsoleController@calculateTeamElo");
    Route::post("test/", "DedicatedControllerController@test");
    Route::get("/alerts", "GlobalAlertController@getAlerts");
    Route::group(['middleware' => ['jwt.verify']], function () {
        Route::post("/send_email", "NewsletterController@send");
        Route::post("/matchmaking", "ConsoleController@createMatchmakingLadder");
        Route::get("/pages/{pageSlug}/events", "ConsoleController@getPageEvents");
        Route::get("/user/settings", "ConsoleController@getUserSettings");
        Route::post("/token", "ConsoleController@generateToken");
        Route::post("/match/start", "ConsoleController@startMatch");
        Route::get("/user/events", "ConsoleController@getUserEvents");
        Route::get("/events/{slug}", "ConsoleController@getEvent");
        Route::get("/user/access", "ConsoleController@getConsoleAccess");
        Route::put("/match/whitelist", "ConsoleController@updateWhitelist");
        Route::post("/showdown", "ConsoleController@createShowdown");

        Route::post("/weekly/wizard/register", "WeeklyTeamEventController@wizardRegistration");

        Route::post("/challenges/accept/{challengeId}", "ChallengeController@acceptChallenge");
        Route::prefix("scheduler")->group(function() {
            Route::post("/schedule/create_group", "SchedulerController@createGroup");
            Route::post("/schedule/test", "SchedulerController@test");
            Route::get("/actions", "SchedulerController@getScheduledActions");
        });
    });

    Route::middleware([\Modules\Console\Http\Middleware\ApiTokenMiddleware::class])->group(function () {
        Route::get("/test", "ConsoleController@test");
        Route::post("/broadcast/event", "BroadcastController@genericEvent");
        Route::post("/match_alerts", "MatchAlertController@createMatchAlert");
        Route::post("/matchmaking/match", "ConsoleController@startMatchmakingMatch");
        Route::post("/matchmaking/validate", "ConsoleController@validateMatchmakingEvent");
        Route::get("/servers/available", "ConsoleController@getAvailableServers");
        Route::prefix('dedicated_controllers')->group(function () {
            Route::post("/", "DedicatedControllerController@add");
            Route::post("/results", "DedicatedControllerController@addResults");
            Route::put("/status", "DedicatedControllerController@updateStatus");
            Route::post("/match/end", "DedicatedControllerController@endMatch");
            Route::post("/match/cancel", "DedicatedControllerController@cancelMatch");
        });
    });
});
