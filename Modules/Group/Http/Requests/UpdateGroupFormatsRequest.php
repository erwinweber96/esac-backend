<?php


namespace Modules\Group\Http\Requests;


use App\Validation\Rules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Group\Entities\Format;
use Modules\Group\Entities\Group;

/**
 * Class UpdateGroupFormatsRequest
 * @package Modules\Group\Http\Requests
 *
 * @property int $groupId
 * @property array $formats
 */
class UpdateGroupFormatsRequest extends FormRequest
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
            'groupId' => [
                'int',
                'required',
                'exists:'.Group::TABLE_NAME.',id',
            ],
            'formats.*' => [
                'int',
                'required',
                'exists:'.Format::TABLE_NAME.',id',
            ]
        ];
    }
}
