<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Match\Entities\MatchEndCondition;

class CreateMatchEndConditionTable extends Migration
{
    /** @var MatchEndCondition $model */
    private $model;

    /**
     * CreateMatchEndConditionTable constructor.
     */
    public function __construct()
    {
        $this->model = app(MatchEndCondition::class);
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
