<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRequiredGameAccountToEvent extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(\Modules\Event\Entities\Event::TABLE_NAME, function (Blueprint $table) {
            $table->boolean("required_game_account")->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(\Modules\Event\Entities\Event::TABLE_NAME, function (Blueprint $table) {
            $table->dropColumn("required_game_account");
        });
    }
}
