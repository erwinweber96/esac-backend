<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Map\Entities\MapPool;

class CorrectMxIdIndex extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(MapPool::TABLE_NAME, function (Blueprint $table) {
            $table->dropIndex('map_pools_mx_id_index');
            $table->integer("mx_id")->nullable()->change();
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
            $table->integer("mx_id")->index();
        });
    }
}
