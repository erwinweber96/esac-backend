<?php

namespace Modules\Console\Console;

use Illuminate\Console\Command;
use Modules\Console\Api\DedicatedControllerApi;
use Modules\Console\Entities\DedicatedController;
use Modules\Console\Entities\GlobalAlert;
use function Sentry\captureException;

class RestartServers extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'servers:restart';

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

        $alert = new GlobalAlert();
        $alert->message = "Currently restarting servers. Servers will be up by 0:30 UTC.";
        $alert->save();
        $dedicatedControllers = DedicatedController::all();

        /** @var DedicatedControllerApi $api */
        $api = app(DedicatedControllerApi::class);

        /** @var DedicatedController $controller */
        foreach ($dedicatedControllers as $controller) {
            try {
                $api->cancelMatch($controller->port);
            } catch (\Throwable $exception) {
                captureException($exception);
                continue;
            }
        }
    }
}
