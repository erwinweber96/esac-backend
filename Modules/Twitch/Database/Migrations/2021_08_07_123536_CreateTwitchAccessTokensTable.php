<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTwitchAccessTokensTable extends Migration
{
    /** @var \Modules\Twitch\Entities\TwitchAccessToken */
    private $model;

    /**
     * Create\Modules\Twitch\Entities\TwitchAccessTokenTable constructor.
     */
    public function __construct()
    {
        $this->model = app(\Modules\Twitch\Entities\TwitchAccessToken::class);
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
