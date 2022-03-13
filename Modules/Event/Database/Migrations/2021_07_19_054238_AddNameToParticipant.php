<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNameToParticipant extends Migration
{
    private $column = "name";

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(\Modules\Event\Entities\Participant::TABLE_NAME, function (Blueprint $table) {
            $table->string($this->column)->nullable();
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
            $table->dropColumn([$this->column]);
        });
    }
}
