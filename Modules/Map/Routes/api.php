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

Route::prefix('map_pools')->group(function() {
    Route::get("/{id}", "MapPoolController@get");
    Route::get("/matches/{matchId}/orders", "MapPoolOrderController@getByMatchId");
    Route::get("/tmx/{mappackId}", "TMXController@getTracks");

    Route::group(['middleware' => ['jwt.verify']], function() {
        Route::post("/create", "MapPoolController@create");
        Route::post("/add/custom", "MapPoolController@addCustomMapPool");
        Route::put("/name", "MapPoolController@updateMapPoolName");
        Route::delete("/{id}", "MapPoolController@delete");
        Route::post("/orders", "MapPoolOrderController@save");
        Route::delete("/matches/{matchId}/orders", "MapPoolOrderController@delete");
    });
});
