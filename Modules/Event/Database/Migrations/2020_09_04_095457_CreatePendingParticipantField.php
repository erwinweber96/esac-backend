<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePendingParticipantField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(\Modules\Event\Entities\Participant::TABLE_NAME, function (Blueprint $table) {
            $table->boolean("pending")->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(\Modules\Event\Entities\Participant::TABLE_NAME, function (Blueprint $table) {
            $table->dropColumn("pending");
        });
    }
}
