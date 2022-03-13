<?php

namespace Modules\Group\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Group\Entities\FormatType;

class GroupDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        FormatType::create([
//            "id"    => FormatType::OTHER_VALUE,
            "text"  => FormatType::OTHER_NAME,
            "value" => FormatType::OTHER_VALUE
        ]);

        FormatType::create([
//            "id"    => FormatType::TIME_ATTACK_VALUE,
            "text"  => FormatType::TIME_ATTACK_NAME,
            "value" => FormatType::TIME_ATTACK_VALUE
        ]);

        FormatType::create([
//            "id"    => FormatType::ROUNDS_VALUE,
            "text"  => FormatType::ROUNDS_NAME,
            "value" => FormatType::ROUNDS_VALUE
        ]);

        FormatType::create([
//            "id"    => FormatType::CUP_VALUE,
            "text"  => FormatType::CUP_NAME,
            "value" => FormatType::CUP_VALUE
        ]);

        FormatType::create([
//            "id"    => FormatType::LAPS_VALUE,
            "text"  => FormatType::LAPS_NAME,
            "value" => FormatType::LAPS_VALUE
        ]);
    }
}
