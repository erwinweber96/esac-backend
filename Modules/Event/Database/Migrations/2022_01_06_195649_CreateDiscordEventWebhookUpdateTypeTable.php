<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDiscordEventWebhookUpdateTypeTable extends Migration
{
    /** @var \Modules\Event\Entities\DiscordEventWebhookUpdateType */
    private $model;

    public function __construct()
    {
        $this->model = app(\Modules\Event\Entities\DiscordEventWebhookUpdateType::class);
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
