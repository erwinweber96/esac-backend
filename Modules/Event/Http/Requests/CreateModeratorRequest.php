<?php


namespace Modules\Event\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Modules\Page\Entities\PageMember;

/**
 * Class CreateModeratorRequest
 * @package Modules\Event\Http\Requests
 *
 * @property int $memberId
 */
class CreateModeratorRequest extends FormRequest
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
            'memberId' => [
                'integer',
                'required',
                'exists:'.PageMember::TABLE_NAME.',id',
            ]
        ];
    }
}
