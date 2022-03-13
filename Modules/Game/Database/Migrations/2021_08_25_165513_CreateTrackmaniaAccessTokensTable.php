<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrackmaniaAccessTokensTable extends Migration
{
    /** @var \Modules\Game\Entities\TrackmaniaAccessToken */
    private $model;

    /**
     * Create\Modules\Game\Entities\TrackmaniaAccessTokenTable constructor.
     */
    public function __construct()
    {
        $this->model = app(\Modules\Game\Entities\TrackmaniaAccessToken::class);
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
