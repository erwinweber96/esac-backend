<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Match\Entities\MatchResultProperty;

class CreateMatchResultPropertyTable extends Migration
{
    /** @var MatchResultProperty */
    private $model;

    /**
     * CreateMatchResultPropertyTable constructor.
     */
    public function __construct()
    {
        $this->model = app(MatchResultProperty::class);
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
