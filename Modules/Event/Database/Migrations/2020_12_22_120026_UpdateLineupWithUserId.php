<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Event\Entities\Lineup;
use Modules\Page\Entities\PageMember;

class UpdateLineupWithUserId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(Lineup::TABLE_NAME, function (Blueprint $table) {
            $table->integer("user_id")->nullable()->index();
        });

        $lineup = Lineup::with("pageMember")->get();
        /** @var Lineup $user */
        foreach ($lineup as $user) {
            /** @var PageMember $pageMember */
            $pageMember = PageMember::where("id", $user->pageMemberId)->first();
            $user->userId = $pageMember->user->id;
            $user->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
