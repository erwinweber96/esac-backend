<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupResultTable extends Migration
{
    /** @var \Modules\Group\Entities\GroupResult */
    private $model;

    /**
     * Create\Modules\Group\Entities\GroupResultTable constructor.
     */
    public function __construct()
    {
        $this->model = app(\Modules\Group\Entities\GroupResult::class);
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
