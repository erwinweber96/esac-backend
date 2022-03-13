<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get("/search", function (Request $request) {
    $param = $request->input("param");

    $events = \Illuminate\Support\Facades\DB::table(\Modules\Event\Entities\Event::TABLE_NAME)
        ->where('name', 'LIKE', '%'.$param.'%')
        ->where("private", false)
        ->get([
            "id",
            "slug",
            "name",
            "type",
            "status_id"
        ]);

    $pages = \Illuminate\Support\Facades\DB::table(\Modules\Page\Entities\Page::TABLE_NAME)
        ->where('name', 'LIKE', '%'.$param.'%')
        ->where("private", false)
        ->get([
            "id",
            "slug",
            "name",
            "type_id"
        ]);

    $users = \Illuminate\Support\Facades\DB::table(\Modules\User\Entities\User::TABLE_NAME)
        ->where('nickname', 'LIKE', '%'.$param.'%')
        ->get([
            "id",
            "nickname",
            "nat",
            "badge_id",
            "elo",
            "tm_nickname"
        ]);

    return [
        "results" => [
            "events" => $events,
            "pages" => $pages,
            "users" => $users
        ]
    ];
});
