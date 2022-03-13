<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Group\Entities\Group;

class AddGroupContainerIdToGroup extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(Group::TABLE_NAME, function (Blueprint $table) {
            $table->integer("group_container_id")->index()->nullable();
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
            $table->removeColumn("group_container_id");
        });
    }
}
