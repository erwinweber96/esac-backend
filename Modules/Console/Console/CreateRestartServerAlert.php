<?php

namespace Modules\Console\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Modules\Console\Entities\GlobalAlert;

class CreateRestartServerAlert extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'alert:server_restart';

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
        $alert = new GlobalAlert();
        $alert->message = "Restarting servers at 0:00 UTC. Ongoing matches will be cancelled.";
        $alert->save();
    }
}
