<?php

namespace App\Migration;

use Illuminate\Database\Migrations\Migration as IlluminateMigration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Model\Model;

abstract class Migration extends IlluminateMigration
{
    /** @var Model $model */
    protected $model;

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
