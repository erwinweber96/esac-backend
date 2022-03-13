<?php

namespace App\Model;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;

/**
 * Class Model
 */
abstract class Model extends \Eloquent
{
    /** @var array */
    public $relations = [];

    /**
     * @return void
     */
    abstract public function getTableName():string;

    /**
     * This should NOT be modified unless absolutely necessary.
     * If you want to modify the blueprint, consider creating a new migration.
     *
     * @param Blueprint $table
     *
     * @return void
     */
    abstract public function generateInitialBlueprint(Blueprint $table);


    public function getAttribute($key)
    {
        return parent::getAttribute(Str::snake($key));
    }

    public function setAttribute($key, $value)
    {
        return parent::setAttribute(Str::snake($key), $value);
    }
}
