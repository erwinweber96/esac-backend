<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\User\Entities\UserRole;

class CreateUserRoleTable extends Migration
{
    /** @var UserRole */
    private $model;

    /**
     * CreateUserRoleTable constructor.
     */
    public function __construct()
    {
        $this->model = app(UserRole::class);
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
