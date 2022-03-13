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

Route::prefix('user')->group(function() {
    Route::post("/register", "UserController@register");
    Route::post("/login", "UserController@login");
    Route::post("/logout", "UserController@logout");
    Route::post("/password/reset/request", "UserController@sendResetLink");
    Route::post("/password/reset", "UserController@resetPassword");

    Route::group(['middleware' => ['jwt.verify']], function() {
        Route::get("/", "UserController@getAuthUser");
        Route::get("/details", "UserController@getUserDetails");
        Route::get("/pages", "UserController@getUserPages");
        Route::get("/events", "UserController@getUserEvents");
        Route::put("/profile", "UserController@updateUserProfile");
        Route::put("/password", "UserController@updateUserPassword");
        Route::post("/messages", "UserMessageController@create");
        Route::get("/messages/{channel}", "UserMessageController@getByChannel");
        Route::get("/notifications", "UserNotificationController@get");
        Route::post("/notifications/read", "UserNotificationController@markNotificationsRead");
        Route::get("/pages/member", "UserController@getMemberPages");
        Route::delete("/pages/{pageId}", "UserController@leavePage");
        Route::post("/badges/{badgeId}", "UserController@attachBadge");
        Route::post("/discord", "UserDiscordController@createOrUpdate");


        Route::post("/friend_requests", "FriendRequestController@sendRequest");
        Route::post("/friend_requests/accept", "FriendRequestController@acceptRequest");
        Route::post("/friend_requests/reject", "FriendRequestController@rejectRequest");
    });

    Route::get("/{userId}", "UserController@getUserById");
});

Route::prefix('maniaplanet')->group(function(){
    Route::group(['middleware' => ['jwt.verify']], function() {
        Route::post("/add", "MpAuthController@linkManiaPlanetToUser");
        Route::post("/oauth", "MpAuthController@oAuthResponse");
    });
});

Route::prefix("trackmania")->group(function() {
    Route::group(['middleware' => ['jwt.verify']], function() {
        Route::put("/nickname", "UserController@updateTmNickname");
    });
});
