<?php


namespace Modules\Page\Entities;


use App\Model\Model;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class PageProperty
 * @package Modules\Page\Entities
 *
 * Contains:
 * @property int    $id
 * @property string $key
 * @property string $value
 * @property bool   $readOnly
 *
 * Has:
 *
 *
 *
 * Belongs to:
 * @property Page $page
 */
class PageProperty extends Model
{
    const TABLE_NAME = "page_properties";

    const PLAY_MX_POOL  = "play_mx_pool";
    const MATCHMAKING_MX_POOL = "matchmaking_mx_pool";

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->string("key");
        $table->string("value");
        $table->boolean("read_only");
        $table->integer("page_id")->index();
    }

    public function page()
    {
        return $this->belongsTo(Page::class);
    }
}
