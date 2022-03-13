<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Console\Entities\ScheduledActionFilter;

class CreateScheduledActionFiltersTable extends Migration
{
    /** @var ScheduledActionFilter $model */
    private $model;

    /**
     * CreateScheduledActionFilterTable constructor.
     */
    public function __construct()
    {
        $this->model = app(ScheduledActionFilter::class);
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
