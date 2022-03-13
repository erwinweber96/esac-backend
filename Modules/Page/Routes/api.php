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

Route::prefix('pages')->group(function() {
    Route::get('/debug-sentry', function () {
        throw new Exception('My first Sentry error!');
    });

    Route::post('/esacgg/contact', "ContactController@contact");

    Route::get("/cache/articles", "ArticleCacheController@getArticles");

    Route::prefix("/articles")->group(function () {
        Route::get("/", "ArticleController@get");
        Route::get("/{slug}", "ArticleController@getBySlug");

        Route::group(['middleware' => ['jwt.verify']], function() {
            Route::post("/", "ArticleController@create");
            Route::delete("/{articleId}", "ArticleController@delete");
        });
    });

    Route::get("/", "PageController@getPages");
    Route::get("/types", "PageController@getTypes");
    Route::get("/invite/{token}", "PageController@getPageNameByInviteToken");
    Route::get("{slug}/avatar", "PageAvatarController@get");

    Route::group(['middleware' => ['jwt.verify']], function() {
        Route::post("/create", "PageController@create");
        Route::delete("/members/{memberId}/remove", "PageController@removePageMember");
        Route::put("/{id}", "PageController@update");
        Route::get("/{slug}/invite/token", "PageController@getInviteToken");
        Route::post("{slug}/invite/accept", "PageController@acceptInvite");
        Route::put("{slug}/members/{memberId}/roles", "PageController@updateMemberRoles");
        Route::post("{slug}/avatar", "PageAvatarController@update");
    });

    Route::get("/{slug}", "PageController@get");
});
