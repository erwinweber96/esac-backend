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

Route::prefix('/twitch')->group(function () {

    Route::group(['middleware' => ['jwt.verify']], function() {
        Route::post("/", "TwitchController@save");
        Route::get("/user", "TwitchController@getUser");
        Route::delete("/unlink", "TwitchController@unlink");
    });

    Route::get("/test", "TwitchController@test");
});
