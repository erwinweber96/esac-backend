<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Event\Entities\Participant;

class UpdatePageAndUserParticipantAttributesAsNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(Participant::TABLE_NAME, function (Blueprint $table) {
            $table->integer("user_id")->nullable()->change();
            $table->integer("page_id")->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(Participant::TABLE_NAME, function (Blueprint $table) {
            $table->integer("user_id")->change();
            $table->integer("page_id")->change();
        });
    }
}
