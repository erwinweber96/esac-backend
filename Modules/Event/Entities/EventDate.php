<?php


namespace Modules\Event\Entities;


use App\Model\Model;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class EventDate
 * @package Modules\Event\Entities
 *
 * Contains:
 * @property int    $id
 * @property string $name
 * @property Carbon $date
 * @property bool   $isStartDate
 * @property bool   $isEndDate
 * @property bool   $isActionDate
 * @property int    $eventId
 *
 * Has:
 *
 *
 * Belongs to:
 * @property Event $event
 */
class EventDate extends Model
{
    const TABLE_NAME        = "event_dates";

    const REGISTRATION_OPEN = "registration_open";
    const EVENT_START       = "event_start";
    const EVENT_END         = "event_end";

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public $fillable = [
        "name",
        "date",
        "is_start_date",
        "is_end_date",
        "is_action_date",
        "event_id"
    ];

    public $timestamps = false;

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->string("name");
        $table->timestamp("date");
        $table->boolean("is_start_date");
        $table->boolean("is_end_date");
        $table->boolean("is_action_date");
        $table->integer("event_id")->index();
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
