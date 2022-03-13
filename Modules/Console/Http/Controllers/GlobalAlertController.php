<?php

namespace Modules\Console\Http\Controllers;

use Modules\Console\Entities\GlobalAlert;

class GlobalAlertController
{
    public function getAlerts()
    {
        return GlobalAlert::all();
    }
}
