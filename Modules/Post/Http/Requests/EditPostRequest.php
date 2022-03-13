<?php


namespace Modules\Post\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Modules\Event\Entities\Event;
use Modules\Page\Entities\Page;
use Modules\Post\Entities\Post;
use Modules\User\Entities\User;

/**
 * Class EditPostRequest
 * @package Modules\Post\Http\Requests
 *
 * @property int    $id
 * @property string $content
 * @property int    $eventId
 * @property int    $userId
 * @property int    $pageId
 */
class EditPostRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id' => [
                'int',
                'exists:'.Post::TABLE_NAME.',id',
            ],
            'content' => [
                'string',
                'required',
                'min:3',
                'max:2000'
            ],
            'eventId' => [
                'int',
                'exists:'.Event::TABLE_NAME.',id',
            ],
            'userId' => [
                'int',
                'exists:'.User::TABLE_NAME.',id',
            ],
            'pageId' => [
                'int',
                'exists:'.Page::TABLE_NAME.',id',
            ],
        ];
    }
}
