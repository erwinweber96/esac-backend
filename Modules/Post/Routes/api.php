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

Route::prefix('posts')->group(function() {
    Route::get("/", "PostController@getPosts");
    Route::get("/{postId}", "PostController@get");

    Route::get("/cache/posts", "PostCacheController@getPosts");

    Route::group(['middleware' => ['jwt.verify']], function() {
        Route::post("/create", "PostController@create");
        Route::put("/update", "PostController@update");
        Route::delete("/{postId}", "PostController@delete");
    });
});
