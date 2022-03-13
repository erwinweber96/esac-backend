<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Console\Entities\DedicatedControllerProperty;

class CreateDedicatedControllerPropertyTable extends Migration
{
    /** @var DedicatedControllerProperty $model */
    private $model;

    /**
     * CreateDedicatedControllerPropertyTable constructor.
     */
    public function __construct()
    {
        $this->model = app(DedicatedControllerProperty::class);
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
