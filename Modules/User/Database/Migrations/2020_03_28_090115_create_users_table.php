<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Migration\Migration;
use Modules\User\Entities\User;

class CreateUsersTable extends Migration
{
    /**
     * CreateUsersTable constructor.
     */
    public function __construct()
    {
        $this->model = app(User::class);
    }
}
