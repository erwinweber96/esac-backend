<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCaseDropTable extends Migration
{
    /** @var \Modules\Play\Entities\CaseDrop $model */
    private $model;

    /**
     * Create\Modules\Play\Entities\CaseDropTable constructor.
     */
    public function __construct()
    {
        $this->model = app(\Modules\Play\Entities\CaseDrop::class);
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
