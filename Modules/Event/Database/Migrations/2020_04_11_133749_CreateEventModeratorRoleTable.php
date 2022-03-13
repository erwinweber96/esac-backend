<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Event\Entities\EventModeratorRole;

class CreateEventModeratorRoleTable extends Migration
{
    /** @var EventModeratorRole */
    private $model;

    /**
     * CreateEventRoleTable constructor.
     */
    public function __construct()
    {
        $this->model = app(EventModeratorRole::class);
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
