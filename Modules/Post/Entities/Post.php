<?php


namespace Modules\Post\Entities;


use App\Model\Model;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Modules\Event\Entities\Event;
use Modules\Page\Entities\Page;
use Modules\User\Entities\User;

/**
 * Class Post
 * @package Modules\Post\Entities
 *
 * Contains:
 * @property int    $id
 * @property string $content
 * @property int    $userId
 * @property int    $pageId
 * @property int    $eventId
 * @property Carbon $createdAt
 * @property Carbon $updatedAt
 *
 * Has:
 *
 *
 * Belongs to:
 * @property User  $user
 * @property Page  $page
 * @property Event $event
 */
class Post extends Model
{
    const TABLE_NAME = "posts";

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->text("content");
        $table->timestamps();
        $table->integer("user_id")->nullable()->index();
        $table->integer("page_id")->nullable()->index();
        $table->integer("event_id")->nullable()->index();
    }

    public $fillable = [
        "content",
        "user_id",
        "page_id",
        "event_id"
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
