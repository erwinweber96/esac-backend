<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Group\Entities\Group;

class UpdateGroupWithIsTypeTree extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(Group::TABLE_NAME, function (Blueprint $table) {
            $table->boolean("is_type_tree")->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(Group::TABLE_NAME, function (Blueprint $table) {
            $table->removeColumn("is_type_tree");
        });
    }
}
