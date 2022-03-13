<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRegistrationOpenField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(\Modules\Event\Entities\Event::TABLE_NAME, function (Blueprint $table) {
            $table->boolean("registration_open")->default(false);
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
            $table->dropColumn("registration_open");
        });
    }
}
