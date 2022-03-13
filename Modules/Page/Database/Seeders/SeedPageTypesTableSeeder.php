<?php

namespace Modules\Page\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Page\Entities\PageType;

class SeedPageTypesTableSeeder extends Seeder
{
    const TEXT_ORGANIZER = "Organizer";
    const TEXT_TEAM = "Team";
    const TEXT_CONTENT_CREATOR = "Content Creator";
    const TEXT_ASSOCIATION = "Association";
    const TEXT_INFLUENCER = "Influencer";

    const VALUE_ORGANIZER = "organizer";
    const VALUE_TEAM = "team";
    const VALUE_CONTENT_CREATOR = "contentCreator";
    const VALUE_ASSOCIATION = "association";
    const VALUE_INFLUENCER = "influencer";

    const PAGE_TYPES = [
        [
            "text" => self::TEXT_TEAM,
            "value" => self::VALUE_TEAM
        ],
        [
            "text" => self::TEXT_ASSOCIATION,
            "value" => self::VALUE_ASSOCIATION
        ],
        [
            "text" => self::TEXT_INFLUENCER,
            "value" => self::VALUE_INFLUENCER
        ],
        [
            "text" => self::TEXT_ORGANIZER,
            "value" => self::VALUE_ORGANIZER
        ],
        [
            "text" => self::TEXT_CONTENT_CREATOR,
            "value" => self::VALUE_CONTENT_CREATOR
        ]
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        foreach (self::PAGE_TYPES as $pageType) {
            PageType::updateOrCreate($pageType);
        }
    }
}
