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

Route::prefix('play')->group(function() {
    Route::prefix("v2")->group(function() {
        Route::get("/showdowns/{slug}", "PlayV2Controller@getShowdown");
    });

    Route::get("/events", "PlayController@getEvents");
    Route::get("/hourly_showdowns", "PlayController@getHourlyShowdowns");
    Route::get("/hourly_showdowns/upcoming", "PlayController@getUpcomingHourlyShowdown");
    Route::get("/cache/leaderboard", "PlayCacheController@getLeaderboard");
    Route::get("/events/{slug}", "PlayController@getPlayEvent");
    Route::get("/leaderboard", "PlayController@getLeaderboard");
    Route::get("/user/{userId}/history", "PlayController@getPlayerEloHistory");
    Route::get("/achievements/properties", "AchievementController@getAchievementProperties");


    Route::group(['middleware' => ['jwt.verify']], function() {
        Route::get("/achievements/completions", "AchievementController@getCompletions");
        Route::post("/cases/open/{id}", "CaseController@openCase");
        Route::get("/achievements/{achievementId}", "AchievementController@getCompletion");
        Route::post("/achievements/{achievementId}/redeem", "AchievementController@redeem");
        Route::post("/cases/seen", "CaseController@markDropsAsSeen");
    });

});
