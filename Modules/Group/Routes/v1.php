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

Route::prefix('groups')->middleware([ApiTokenMiddleware::class])->group(function () {
    Route::post("/create", "GroupController@create");
    Route::put("/participants", "GroupController@updateParticipants");
    Route::delete("/{id}", "GroupController@delete");
    Route::get("/participants", "GroupController@getGroupParticipants");
});
