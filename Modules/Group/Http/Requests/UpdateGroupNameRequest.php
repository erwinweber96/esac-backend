<?php


namespace Modules\Group\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Modules\Group\Entities\Group;

/**
 * Class UpdateGroupNameRequest
 * @package Modules\Group\Http\Requests
 *
 * @property int $groupId
 * @property string $groupName
 */
class UpdateGroupNameRequest extends FormRequest
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
            'groupName' => [
                'string',
                'required',
                'min:3',
                'max:100'
            ]
        ];
    }
}
