<?php


namespace Modules\Event\Entities;


use App\Model\Model;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class EventFaq
 * @package Modules\Event\Entities
 *
 * Contains:
 * @property int    $id
 * @property string $question
 * @property string $answer
 *
 * Has:
 *
 *
 * Belongs to:
 * @property Event $event
 */
class EventFaq extends Model
{
    const TABLE_NAME = "event_faq";

    /*
     * Overrides default table naming
     */
    protected $table = self::TABLE_NAME;

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->text("question");
        $table->text("answer");
        $table->integer("event_id")->index();
        $table->timestamps();
    }

    public $fillable = [
        "question",
        "answer",
        "event_id"
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
