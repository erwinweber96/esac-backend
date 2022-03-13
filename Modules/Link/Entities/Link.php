<?php


namespace Modules\Link\Entities;


use App\Model\Model;
use Illuminate\Database\Schema\Blueprint;
use Modules\Event\Entities\Event;
use Modules\Page\Entities\Page;

/**
 * Class Link
 * @package Modules\Link\Entities
 *
 * Contains:
 * @property int     $id
 * @property string  $url
 * @property string  $name
 * @property boolean $pending
 * @property int     $eventId
 * @property int     $pageId
 * @property int     $typeId
 *
 * Has:
 * @property LinkType $type
 *
 * Belongs to:
 * @property Event $event
 * @property Page  $page
 */
class Link extends Model
{
    const TABLE_NAME = "links";

    const TYPE_SPREADSHEET = 1;

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->timestamps();
        $table->string("url");
        $table->string("name");
        $table->boolean("pending");
        $table->integer("type_id")->nullable()->index();
        $table->integer("event_id")->nullable()->index();
        $table->integer("page_id")->nullable()->index();
    }

    public $fillable = [
        "url",
        "name",
        "pending",
        "type_id",
        "event_id",
        "page_id"
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function page()
    {
        return $this->belongsTo(Page::class);
    }
}
