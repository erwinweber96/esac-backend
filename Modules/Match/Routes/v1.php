<?php


use Illuminate\Http\Request;
use Modules\Console\Http\Middleware\ApiTokenMiddleware;

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

Route::prefix('matches')->middleware([ApiTokenMiddleware::class])->group(function () {
    Route::get("/participants", "MatchController@getMatchParticipants");
    Route::post("/", "MatchController@create");
    Route::put("/participants", "MatchController@updateParticipants");
    Route::post("/results", "MatchController@createResult");
    Route::delete("/results/{id}", "MatchController@deleteResult");
    Route::post("/game_servers", "MatchController@addGameServer");
    Route::delete("/{matchId}/game_servers/{serverId}", "MatchController@removeGameServer");
    Route::put("/{matchId}/status/{statusId}", "MatchController@updateStatus");
});
