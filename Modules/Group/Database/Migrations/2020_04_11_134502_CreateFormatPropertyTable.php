<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Group\Entities\FormatProperty;

class CreateFormatPropertyTable extends Migration
{
    /** @var FormatProperty */
    private $model;

    /**
     * CreateFormatPropertyTable constructor.
     */
    public function __construct()
    {
        $this->model = app(FormatProperty::class);
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
