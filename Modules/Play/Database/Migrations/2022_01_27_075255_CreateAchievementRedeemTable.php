<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAchievementRedeemTable extends Migration
{
    /** @var \Modules\Play\Entities\AchievementRedeem $model */
    private $model;

    /**
     * Create\Modules\Play\Entities\AchievementRedeemTable constructor.
     */
    public function __construct()
    {
        $this->model = app(\Modules\Play\Entities\AchievementRedeem::class);
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
