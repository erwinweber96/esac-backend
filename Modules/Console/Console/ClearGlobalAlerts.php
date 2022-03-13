<?php

namespace Modules\Console\Console;

use Illuminate\Console\Command;
use Modules\Console\Entities\GlobalAlert;

class ClearGlobalAlerts extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'alerts:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $alerts = GlobalAlert::all();

        /** @var GlobalAlert $alert */
        foreach ($alerts as $alert) {
            $alert->delete();
        }
    }
}
