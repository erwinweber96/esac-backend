<?php


namespace Modules\Map\Http\Requests;


use App\Validation\Rules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Event\Entities\Event;

/**
 * Class CreateMapPoolRequest
 * @package Modules\Map\Http\Requests
 *
 * @property string $name
 * @property int $eventId
 * @property int $mxId
 */
class CreateMapPoolRequest extends FormRequest
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
            'eventId' => [
                'integer',
                'required',
                'exists:'.Event::TABLE_NAME.',id',
            ],
            'mxId' => [
                'integer',
                'required'
            ]
        ];
    }
}
