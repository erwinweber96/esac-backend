<?php


namespace Modules\Page\Entities;


use App\Model\Model;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class PageType
 * @package Modules\Page\Entities
 *
 * Contains:
 * @property int    $id
 * @property string $text
 * @property string $value
 *
 * Has:
 * @property Page[] $pages
 *
 * Belongs to:
 *
 */
class PageType extends Model
{
    const TEXT_ORGANIZER = "Organizer";
    const TEXT_TEAM = "Team";
    const TEXT_CONTENT_CREATOR = "Content Creator";
    const TEXT_ASSOCIATION = "Association";
    const TEXT_INFLUENCER = "Influencer";

    const VALUE_ORGANIZER = 4;
    const VALUE_TEAM = 1;
    const VALUE_CONTENT_CREATOR = 5;
    const VALUE_ASSOCIATION = 2;
    const VALUE_INFLUENCER = 3;

    const PAGE_TYPE_VALUES = [
        self::VALUE_ASSOCIATION,
        self::VALUE_CONTENT_CREATOR,
        self::VALUE_INFLUENCER,
        self::VALUE_ORGANIZER,
        self::VALUE_TEAM
    ];

    const PAGE_TYPE_TEXTS = [
        self::TEXT_ASSOCIATION,
        self::TEXT_CONTENT_CREATOR,
        self::TEXT_INFLUENCER,
        self::TEXT_ORGANIZER,
        self::TEXT_TEAM
    ];

    const TABLE_NAME = "page_types";

    public $timestamps = false;

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->string("text");
        $table->string("value");
    }

    public function events()
    {
        return $this->hasMany(Page::class);
    }
}
