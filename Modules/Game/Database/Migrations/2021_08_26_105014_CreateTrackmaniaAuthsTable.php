<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrackmaniaAuthsTable extends Migration
{
    /** @var \Modules\Game\Entities\TrackmaniaAuth */
    private $model;

    /**
     * Create\Modules\Game\Entities\\Modules\Game\Entities\TrackmaniaAuthTable constructor.
     */
    public function __construct()
    {
        $this->model = app(\Modules\Game\Entities\TrackmaniaAuth::class);
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
