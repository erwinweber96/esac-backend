<?php


namespace Modules\Group\Http\Requests;


use App\Validation\Rules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Event\Entities\Event;
use Modules\Group\Entities\GroupContainer;

/**
 * Class CreateGroupRequest
 * @package Modules\Group\Http\Requests
 *
 * @property string $name
 * @property int    $minSize
 * @property int    $maxSize
 * @property bool   $isTypeTree
 * @property int    $eventId
 * @property int    $containerId
 */
class CreateGroupRequest extends FormRequest
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
                Rule::notIn(Rules::FORBIDDEN_SLUGS),
            ],
            'isTypeTree' => [
                'boolean',
                'required'
            ],
            'eventId' => [
                'int',
                'required',
                'exists:'.Event::TABLE_NAME.',id',
            ],
            'containerId' => [
                'int',
                'nullable',
                'exists:'.GroupContainer::TABLE_NAME.',id'
            ]
        ];
    }
}
