<?php


namespace Modules\Event\Http\Requests;


use App\Validation\Rules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Event\Entities\Event;

/**
 * Class RegisterParticipantRequest
 * @package Modules\Event\Http\Requests
 *
 * @property int $eventId
 * @property int $participantId
 */
class RegisterParticipantRequest extends FormRequest
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
            ],
            'participantId' => [
                'integer',
                'required'
            ]
        ];
    }
}
