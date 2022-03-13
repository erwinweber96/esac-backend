<?php


namespace Modules\Match\Http\Requests;


use App\Validation\Rules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Event\Entities\Event;
use Modules\Group\Entities\Group;
use Modules\Map\Entities\MapPool;

/**
 * Class CreateMatchRequest
 * @package Modules\Match\Http\Requests
 *
 * @property string $name
 * @property int $groupId
 * @property string $date
 * @property string $time
 * @property int $mapPoolId
 */
class CreateMatchRequest extends FormRequest
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
            'groupId' => [
                'integer',
                'required',
                'exists:'.Group::TABLE_NAME.',id',
            ],
            'date' => [
                'string',
                'required'
            ],
            'time' => [
                'string',
                'required'
            ],
            'mapPoolId' => [
                'integer',
                'exists:'.MapPool::TABLE_NAME.',id',
            ]
        ];
    }
}
