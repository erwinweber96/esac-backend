<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTimeResultTable extends Migration
{
    /** @var \Modules\Match\Entities\TimeResult $model */
    private $model;

    /**
     * Create\Modules\Match\Entities\TimeResultsTable constructor.
     */
    public function __construct()
    {
        $this->model = app(\Modules\Match\Entities\TimeResult::class);
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
