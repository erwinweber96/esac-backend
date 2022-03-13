<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Event\Entities\Event;

class AddPrivateToEventTable extends Migration
{
    private $column = "private";

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(Event::TABLE_NAME, function (Blueprint $table) {
            $table->boolean($this->column)->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(Event::TABLE_NAME, function (Blueprint $table) {
            $table->dropColumn([$this->column]);
        });
    }
}
