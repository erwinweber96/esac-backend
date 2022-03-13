<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRefreshTokenToTwitchAccessToken extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(\Modules\Twitch\Entities\TwitchAccessToken::TABLE_NAME, function (Blueprint $table) {
            $table->string("refresh_token");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(\Modules\Twitch\Entities\TwitchAccessToken::TABLE_NAME, function (Blueprint $table) {
            $table->dropColumn("refresh_token");
        });
    }
}
