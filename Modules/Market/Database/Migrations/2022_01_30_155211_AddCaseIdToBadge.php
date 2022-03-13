<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCaseIdToBadge extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(\Modules\Market\Entities\Badge::TABLE_NAME, function (Blueprint $table) {
            $table->integer("case_id")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(\Modules\Market\Entities\Badge::TABLE_NAME, function (Blueprint $table) {
            $table->dropColumn("case_id");
        });
    }
}
