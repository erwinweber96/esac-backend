<?php


namespace Modules\Console\Entities;


use App\Model\Model;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Modules\Event\Entities\Event;
use Modules\User\Entities\User;

/**
 * Class ScheduledAction
 * @package Modules\Console\Entities
 *
 * @property int    $id
 * @property string $name
 * @property string $class
 * @property string $data
 * @property int    $typeId
 * @property string $actionDateStart
 * @property string $actionDateEnd
 * @property int    $cronTypeId
 * @property bool   $active
 * @property Carbon $updatedAt
 * @property Carbon $createdAt
 * @property int    $userId
 *
 * @property Event                   $event
 * @property ScheduledActionFilter[] $scheduledActionFilters
 * @property User                    $user
 */
class ScheduledAction extends Model
{
    const TABLE_NAME = "scheduled_actions";

    const TYPE_SINGLE_ACTION    = 1;
    const TYPE_RECURRENT_ACTION = 2;

    const TYPE_CRON_EVERY_MINUTE          = 1;
    const TYPE_CRON_EVERY_FIVE_MINUTES    = 5;
    const TYPE_CRON_EVERY_TEN_MINUTES     = 10;
    const TYPE_CRON_EVERY_FIFTEEN_MINUTES = 15;
    const TYPE_CRON_EVERY_THIRTY_MINUTES  = 30;
    const TYPE_CRON_EVERY_HOUR            = 60;
    const TYPE_CRON_EVERY_DAY             = 1440;
    const TYPE_CRON_EVERY_WEEK            = 10080;

    protected $fillable = [
        "name",
        "class",
        "data",
        "type_id",
        "action_date_start",
        "action_date_end",
        "cron_type_id",
        "active",
        "user_id"
    ];

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->string("name");
        $table->string("class");
        $table->text("data")->nullable();
        $table->integer("type_id");
        $table->timestamp("action_date_start")->nullable();
        $table->timestamp("action_date_end")->nullable();
        $table->integer("cron_type_id")->nullable();
        $table->boolean("active")->default(false);
        $table->timestamps();
    }

    public function scheduledActionFilters()
    {
        return $this->hasMany(ScheduledActionFilter::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
