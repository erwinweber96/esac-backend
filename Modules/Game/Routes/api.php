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

Route::prefix('/game')->group(function () {
    Route::group(['middleware' => ['jwt.verify']], function() {
        Route::prefix("/trackmania")->group(function () {
            Route::post("/auth", "TrackmaniaAuthController@save");
        });
    });
});
