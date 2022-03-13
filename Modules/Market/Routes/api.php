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

Route::prefix('market')->group(function() {
    //unverified routes
    Route::get("/badges", "MarketController@getMarketBadges");
    Route::get("/cache/badges", "MarketCacheController@getBadges");

    Route::group(['middleware' => ['jwt.verify']], function () {
        Route::post("/badges/purchase/{badgeId}", "MarketController@purchaseBadge");
    });
});
