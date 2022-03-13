<?php

namespace Modules\Console\Console;

use Illuminate\Console\Command;
use Modules\Console\Api\DedicatedControllerApi;
use Modules\Console\Entities\DedicatedController;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class PingDedicatedControllers extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'dedicated_controllers:ping';

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
     *
     * @param DedicatedControllerApi $api
     * @return mixed
     */
    public function handle(DedicatedControllerApi $api)
    {
        $servers = DedicatedController::all();

        /** @var DedicatedController $server */
        foreach ($servers as $server) {
            $isUp = $api->isServerUp($server->port);

            if (!$isUp) {
                try {
                    $server->delete();
                } catch (\Throwable $exception) {
                    //
                }
                //TODO: restart docker container maybe, but there could be an issue with the server though
                //TODO: what to do?
            }
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [];
    }
}
