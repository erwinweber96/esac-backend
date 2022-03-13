<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGlobalAlertsTable extends Migration
{
    /** @var \Modules\Console\Entities\GlobalAlert $model */
    private $model;

    /**
     * Create\Modules\Console\Entities\GlobalAlertTable constructor.
     */
    public function __construct()
    {
        $this->model = app(\Modules\Console\Entities\GlobalAlert::class);
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
