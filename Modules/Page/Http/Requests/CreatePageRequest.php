<?php


namespace Modules\Page\Http\Requests;


use App\Validation\Rules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Page\Entities\PageType;

class CreatePageRequest extends FormRequest
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
            'name' => [
                'string',
                'required',
                'max:100',
                'min:3',
                'unique:pages',
                Rule::notIn(Rules::FORBIDDEN_SLUGS)
            ],
            'about' => [
                'string',
                'required'
            ],
            'type_id' => [
                'int',
                'required',
                Rule::in(PageType::PAGE_TYPE_VALUES)
            ]
        ];
    }
}
