<?php


namespace Modules\Event\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Modules\Event\Entities\Event;

/**
 * Class GetEventParticipantsRequest
 * @package Modules\Event\Http\Requests
 *
 * @property integer $eventId
 */
class GetEventParticipantsRequest extends FormRequest
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
            'eventId' => [
                'integer',
                'required',
                'exists:'.Event::TABLE_NAME.',id',
            ]
        ];
    }
}
