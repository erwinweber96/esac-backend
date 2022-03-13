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

Route::prefix('events')->middleware([ApiTokenMiddleware::class])->group(function() {
    Route::put("/status", "EventController@updateStatus");
    Route::get("/participants", "EventController@getEventParticipants");
});
