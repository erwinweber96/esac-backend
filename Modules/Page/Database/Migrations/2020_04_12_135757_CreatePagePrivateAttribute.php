<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Page\Entities\Page;

class CreatePagePrivateAttribute extends Migration
{
    private $column = "private";

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(Page::TABLE_NAME, function (Blueprint $table) {
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
        Schema::table(Page::TABLE_NAME, function (Blueprint $table) {
            $table->dropColumn([$this->column]);
        });
    }
}
