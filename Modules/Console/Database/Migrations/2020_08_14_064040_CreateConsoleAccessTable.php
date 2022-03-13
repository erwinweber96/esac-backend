<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Console\Entities\ConsoleAccess;

class CreateConsoleAccessTable extends Migration
{
    /** @var ConsoleAccess $model */
    private $model;

    /**
     * CreateConsoleAccessTable constructor.
     */
    public function __construct()
    {
        $this->model = app(ConsoleAccess::class);
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
