<?php


namespace Modules\Page\Entities;


use App\Model\Model;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class Article
 * @package Modules\Page\Entities
 *
 * @property int    $id
 * @property string $title
 * @property string $content
 * @property int    $pageId
 * @property bool   $public
 * @property string $slug
 * @property string $largeImgUrl
 * @property string $smallImgUrl
 * @property string $updatedAt
 * @property string $createdAt
 *
 * @property Page $page
 */
class Article extends Model
{
    const TABLE_NAME = "articles";

    protected $fillable = [
        "title",
        "content",
        "page_id",
        "slug",
        "large_img_url",
        "small_img_url",
        "updated_at",
        "created_at",
        "public"
    ];

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->string("title");
        $table->text("content");
        $table->integer("page_id")->index();
        $table->boolean("public")->default(false);
        $table->string("slug")->index();
        $table->string("large_img_url");
        $table->string("small_img_url");
        $table->timestamps();
    }

    public function page()
    {
        return $this->belongsTo(Page::class);
    }
}
