<?php


namespace Modules\Page\Http\Requests;


use App\Validation\Rules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Page\Entities\PageType;

/**
 * Class CreateArticleRequest
 * @package Modules\Page\Http\Requests
 *
 * @property string $title
 * @property string $content
 * @property int    $pageId
 * @property string $largeImgUrl
 * @property string $smallImgUrl
 */
class CreateArticleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
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
            'title' => [
                'string',
                'required',
                'max:100',
                'min:7',
                'unique:articles',
                Rule::notIn(Rules::FORBIDDEN_SLUGS)
            ],
            'content' => [
                'string',
                'required'
            ],
            'pageId' => [
                'int',
                'required'
            ],
            'largeImgUrl' => [
                'string',
                'required',
                'max:150',
                'min:10'
            ],
            'smallImgUrl' => [
                'string',
                'required',
                'max:150',
                'min:10'
            ]
        ];
    }
}
