<?php


namespace Modules\Play\Console;


use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Modules\Play\Http\Controllers\PlayController;

class CacheLeaderboard extends Command
{
    const COMMAND = "leaderboard:cache";

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = self::COMMAND;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Caches the leaderboard on S3.';

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
     * @throws \Exception
     */
    public function handle()
    {
        /** @var PlayController $playController */
        $playController = app(PlayController::class);

        $leaderboard = $playController->getLeaderboard();

        return Storage::disk("do_spaces")
            ->put("/cache/play_leaderboard.json", json_encode($leaderboard), "public");
    }
}
