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

Route::prefix('matches')->group(function() {
    //unverified routes
    Route::get("/{id}", "MatchController@get");
    Route::get("/", "MatchController@getFeaturedMatches");
    Route::get("/alerts/{matchId}", "MatchAlertController@getMatchAlerts");

    Route::group(["middleware" => [\Modules\Console\Http\Middleware\ApiTokenMiddleware::class]], function() {
        Route::post("/time_results", "TimeResultController@createResult");
        Route::get("/time_results/{matchId}", "TimeResultController@getTimeResults");
    });

    Route::group(['middleware' => ['jwt.verify']], function() {
        Route::post("/", "MatchController@create");
        Route::put("/participants", "MatchController@updateParticipants");
        Route::put("/formats", "MatchController@updateFormats");
        Route::put("/name", "MatchController@updateMatchName");
        Route::delete("{id}", "MatchController@delete");
        Route::put("/{matchId}/status/{statusId}", "MatchController@updateStatus");

        Route::post("/results", "MatchController@createResult");
        Route::put("/results", "MatchController@updateResult");
        Route::delete("/results/{id}", "MatchController@deleteResult");
        Route::post("/results/approve", "MatchController@approveResult");

        Route::post("/game_servers", "MatchController@addGameServer");
        Route::post("/game_servers/approve", "MatchController@approveGameServer");
        Route::delete("/{matchId}/game_servers/{serverId}", "MatchController@removeGameServer");

        Route::post("/live_streams", "MatchController@addLiveStream");
        Route::post("/live_streams/approve", "MatchController@approveLiveStream");
        Route::delete("/live_streams/{streamId}", "MatchController@removeLiveStream");
        Route::delete("/live_streams/{streamId}", "MatchController@removeLiveStream");

        Route::post("/vods", "MatchController@addVod");
        Route::post("/vods/approve", "MatchController@approveVod");
        Route::delete("/vods/{vodId}", "MatchController@removeVod");

        Route::post("/match_end_conditions", "MatchEndConditionController@create");

        Route::post("/properties", "MatchPropertyController@create");
        Route::get("/properties/{matchId}", "MatchPropertyController@get");
        Route::delete("/properties/{propertyId}", "MatchPropertyController@delete");

        Route::get("{matchId}/play/server", "MatchController@getPlayServerData");

        Route::put("/{matchId}", "MatchController@update");

        Route::post("/alerts", "MatchAlertController@createMatchAlert");
        Route::delete("/alerts/{id}", "MatchAlertController@deleteMatchAlert");
    });
});
