<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Map\Entities\MapPool;

class AddCustomMapPoolOption extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(MapPool::TABLE_NAME, function (Blueprint $table) {
            $table->boolean("custom")->default(false);
            $table->string("link")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(MapPool::TABLE_NAME, function (Blueprint $table) {
            $table->dropColumn("custom");
            $table->dropColumn("link");
        });
    }
}
