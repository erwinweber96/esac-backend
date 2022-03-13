<?php

namespace Modules\Game\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Game\Entities\Game;

class GameDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        Game::create([
            "id" => 1,
            "name" => "Trackmania"
        ]);

        Game::create([
            "id" => 2,
            "name" => "TrackMania 2 Stadium"
        ]);
        // $this->call("OthersTableSeeder");
    }
}
