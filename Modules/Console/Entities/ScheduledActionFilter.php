<?php


namespace Modules\Console\Entities;


use App\Model\Model;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class ScheduledActionFilter
 * @package Modules\Console\Entities
 *
 * @property int             $id
 * @property array           $data                  TODO: json_decode
 * @property string          $class
 * @property int             $scheduledActionId
 * @property bool            $active
 * @property Carbon          $updatedAt
 * @property Carbon          $createdAt
 * @property int             $typeId
 *
 * @property ScheduledAction $scheduledAction
 */
class ScheduledActionFilter extends Model
{
    const TABLE_NAME = "scheduled_action_filters";

    const FILTER_TYPE_MOST_RECENT = 1;
    const FILTER_TYPE_ALL         = 2;

    protected $fillable = [
        'data',
        'class',
        'scheduled_action_id',
        'active',
        'type_id'
    ];

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->text('data')->nullable();
        $table->string("class");
        $table->integer('scheduled_action_id')->index();
        $table->boolean("active");
        $table->integer("type_id");
        $table->timestamps();
    }

    public function scheduledAction()
    {
        $this->belongsTo(ScheduledAction::class);
    }
}
