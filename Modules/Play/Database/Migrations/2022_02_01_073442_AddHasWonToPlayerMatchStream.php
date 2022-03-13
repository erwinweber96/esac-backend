<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHasWonToPlayerMatchStream extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(\Modules\Play\Entities\PlayerMatchStream::TABLE_NAME, function (Blueprint $table) {
            $table->boolean("has_won")->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(\Modules\Play\Entities\PlayerMatchStream::TABLE_NAME, function (Blueprint $table) {
            $table->dropColumn("has_won");
        });
    }
}
