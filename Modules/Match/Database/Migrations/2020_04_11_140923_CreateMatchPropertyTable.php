<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Match\Entities\MatchProperty;

class CreateMatchPropertyTable extends Migration
{
    /** @var MatchProperty */
    private $model;

    /**
     * CreateMatchPropertyTable constructor.
     */
    public function __construct()
    {
        $this->model = app(MatchProperty::class);
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
