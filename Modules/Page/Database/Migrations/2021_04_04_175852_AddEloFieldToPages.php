<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEloFieldToPages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(\Modules\Page\Entities\Page::TABLE_NAME, function (Blueprint $table) {
            $table->integer("elo")->default(\Modules\Page\Entities\Page::DEFAULT_ELO);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(\Modules\Page\Entities\Page::TABLE_NAME, function (Blueprint $table) {
            $table->dropColumn("elo");
        });
    }
}
