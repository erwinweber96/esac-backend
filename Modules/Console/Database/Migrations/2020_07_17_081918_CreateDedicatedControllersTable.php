<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDedicatedControllersTable extends Migration
{
    /** @var \Modules\Console\Entities\DedicatedController $model */
    private $model;

    /**
     * CreateDedicatedControllersTable constructor.
     */
    public function __construct()
    {
        $this->model = app(\Modules\Console\Entities\DedicatedController::class);
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
