<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Console\Entities\ScheduledAction;

class CreateScheduledActionsTable extends Migration
{
    /** @var ScheduledAction $model */
    private $model;

    /**
     * CreateScheduledActionTable constructor.
     */
    public function __construct()
    {
        $this->model = app(ScheduledAction::class);
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
