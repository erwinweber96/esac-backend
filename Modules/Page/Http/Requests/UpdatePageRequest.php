<?php


namespace Modules\Page\Http\Requests;


use App\Validation\Rules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Page\Entities\PageType;

class UpdatePageRequest extends FormRequest
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
            'about' => [
                'string',
                'required'
            ],
            'type_id' => [
                'int',
                'required',
                Rule::in(PageType::PAGE_TYPE_VALUES)
            ],
            'private' => [
                'boolean',
                'required'
            ],
            'name' => [
                'string',
                'max:100',
                'min:7',
                Rule::notIn(Rules::FORBIDDEN_SLUGS)
            ]
        ];
    }
}
