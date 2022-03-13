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

Route::prefix('events')->group(function() {
    Route::get("/test", "EventController@test");

    Route::get("", "EventController@getEvents");
    Route::get("/featured", "EventController@getFeaturedEvents");
    Route::get("/participants/{participantId}/lineup", "LineupController@get");
    Route::get("/filter/property/{key}", "EventController@getEventsWithPropertyKey");
    Route::get("{slug}/avatar", "EventAvatarController@get");
    Route::get("/{slug}/dates", "EventDateController@getEventDates");

    Route::prefix("cache")->group(function() {
        Route::get("/events", "EventCacheController@getEvents");
        Route::get("/featured_events", "EventCacheController@getFeaturedEvents");
        Route::get("/{slug}", "EventCacheController@getEventBySlug");
        Route::get("/test", "EventCacheController@test");
    });

    Route::prefix("v2")->group(function () {
        Route::get("/overview/{slug}", "EventV2Controller@getEventOverview");
        Route::get("/participants/{slug}", "EventV2Controller@getEventParticipants");
        Route::get("/groups/{groupId}/matches", "EventV2Controller@getGroupMatches");
        Route::get("/groups/{slug}", "EventV2Controller@getEventGroups");
        Route::get("/faq/{slug}", "EventV2Controller@getFaq");
        Route::get("/formats/{slug}", "EventV2Controller@getEventFormats");
        Route::post("/overview/{slug}/cache", "EventV2Controller@cacheEventOverview");
    });

    Route::group(['middleware' => ['jwt.verify']], function() {
        Route::post("/create", "EventController@create");
        Route::post("/register", "EventController@register");
        Route::post("/pre_register", "EventController@preRegister");
        Route::post("/withdraw", "EventController@withdraw");
        Route::post("/remove_participant", "EventController@removeParticipant");
        Route::post("/approve_participant", "EventController@approveParticipant");
        Route::post("/faq", "EventFaqController@create");
        Route::delete("/faq/{id}", "EventFaqController@delete");
        Route::put("/start_date", "EventController@editEventStartDate");
        Route::get("/{slug}/pending_submissions", "EventController@getPendingSubmissions");

        Route::put("/status", "EventController@updateStatus");
        Route::put("/{slug}", "EventController@updateEvent");

        Route::post("/{slug}/moderators", "EventController@createModerator");
        Route::put("/{slug}/moderators/{memberId}/roles", "EventController@updateModeratorRoles");

        Route::post("{slug}/avatar", "EventAvatarController@update");

        Route::get("/{slug}/participants/export/csv", "ParticipantController@exportParticipantsToCsv");

        Route::get("/{slug}/properties", "EventPropertyController@get");
        Route::post("/properties", "EventPropertyController@create");
        Route::delete("/properties/{propertyId}", "EventPropertyController@delete");

        Route::post("/links", "EventSocialMediaController@create");
        Route::delete("/links/{linkId}", "EventSocialMediaController@delete");

        Route::post("/lineup", "LineupController@save");

        Route::post("/dates", "EventDateController@saveDate");
        Route::delete("/dates/{id}", "EventDateController@deleteDate");

        Route::post('/participants', "EventController@addNonUser");
        Route::post("/discord/webhook_settings", "DiscordEventWebhookController@save");
        Route::get("/{slug}/discord/webhook_settings", "DiscordEventWebhookController@get");
    });

    Route::middleware([\Modules\Console\Http\Middleware\ApiTokenMiddleware::class])->group(function () {
        Route::get("/{slug}/console/properties", "EventPropertyController@get");
    });

    Route::get("/{slug}", "EventController@get");
});
