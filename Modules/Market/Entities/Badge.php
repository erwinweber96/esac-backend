<?php


namespace Modules\Market\Entities;


use App\Model\Model;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Modules\User\Entities\User;

/**
 * Class Badge
 * @package Market\Entities
 *
 * @property int    $id
 * @property string $name
 * @property string $slug
 * @property string $format
 * @property string $description
 * @property float  $cost
 * @property bool   $isVisible
 * @property bool   $isPurchasable
 * @property int    $caseId
 * @property Carbon $updatedAt
 * @property Carbon $createdAt
 */
class Badge extends Model
{
    const TABLE_NAME = "badges";

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function generateInitialBlueprint(Blueprint $table)
    {
        $table->id();
        $table->string("name");
        $table->string("slug");
        $table->string("format")->default("png");
        $table->text("description");
        $table->float("cost");
        $table->boolean("is_visible");
        $table->boolean("is_purchasable");
        $table->timestamps();
    }

    public function users()
    {
        $this->belongsToMany(User::class);
    }
}
