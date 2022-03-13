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

Route::prefix('formats')->group(function() {
    Route::get("/{slug}", "FormatController@get");

    Route::group(['middleware' => ['jwt.verify']], function() {
        Route::get("/types", "FormatController@getTypes");
        Route::post("/create", "FormatController@create");
        Route::put("/name", "FormatController@updateFormatName");
        Route::delete("/{id}", "FormatController@delete");
        Route::post("/matchsettings", "FormatController@addMatchSettings");
    });
});


Route::prefix('groups')->group(function() {
    Route::get("/{groupId}", "GroupController@get");
    Route::get("/{groupId}/properties", "GroupController@getGroupProperties");

    Route::prefix('containers')->group(function() {
        Route::get("/{id}", "GroupContainerController@get");

        Route::group(["middleware" => ["jwt.verify"]], function() {
            Route::post("/", "GroupContainerController@create");
            Route::put("/", "GroupContainerController@edit");
            Route::post("/generate", "GroupContainerController@generate");
            Route::post("/round_robin", "GroupContainerController@generateRoundRobin");
            Route::post("/swiss", "GroupContainerController@generateSwiss");
            Route::post("/swiss/next", "GroupContainerController@generateNextSwissRound");
            //TODO: delete container
        });
    });

    Route::group(['middleware' => ['jwt.verify']], function() {
        Route::post("/editor", "GroupController@editor");
        Route::post("/create", "GroupController@create");
        Route::put("/formats", "GroupController@updateFormats");
        Route::put("/participants", "GroupController@updateParticipants");
        Route::put("/name", "GroupController@updateGroupName");
        Route::delete("/{id}", "GroupController@delete");
    });
});

Route::prefix('results')->group(function() {
    Route::get("/{eventId}", "GroupResultController@get");

    Route::group(['middleware' => ['jwt.verify']], function() {
        Route::post("/group", "GroupResultController@createGroup");
        Route::post("/", "GroupResultController@createResult");
        Route::delete("/{resultId}", "GroupResultController@deleteResult");
    });
});


