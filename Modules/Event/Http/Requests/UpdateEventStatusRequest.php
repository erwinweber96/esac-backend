<?php


namespace Modules\Event\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Event\Entities\Event;

/**
 * Class UpdateEventStatusRequest
 * @package Modules\Event\Http\Requests
 *
 * @property int $statusId
 * @property int $eventId
 */
class UpdateEventStatusRequest extends FormRequest
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
            'statusId' => [
                'integer',
                'required',
                Rule::in(Event::ALL_STATUS)
            ],
            'eventId' => [
                'integer',
                'required',
                'exists:'.Event::TABLE_NAME.',id',
            ]
        ];
    }
}
