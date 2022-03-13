<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeamEloHistoryTable extends Migration
{
    /** @var \Modules\Page\Entities\TeamEloHistory */
    private $model;

    /**
     * CreateTeamEloHistoryTable constructor.
     */
    public function __construct()
    {
        $this->model = app(\Modules\Page\Entities\TeamEloHistory::class);
    }


    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->model->getTableName(), function (Blueprint $table) {
            $this->model->generateInitialBlueprint($table);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->model->getTableName());
    }
}
