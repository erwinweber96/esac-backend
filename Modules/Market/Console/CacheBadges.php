<?php


namespace Modules\Market\Console;


use Illuminate\Support\Facades\Storage;
use Modules\Market\Entities\Badge;

class CacheBadges extends \Illuminate\Console\Command
{
    const COMMAND = "badges:cache";

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
    protected $description = 'Caches the badges on S3.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $badges = Badge::where("is_visible", "=", true)
            ->where("is_purchasable", '=', true)
            ->get();

        return Storage::disk("do_spaces")
            ->put("/cache/badges.json", json_encode($badges), "public");
    }
}
