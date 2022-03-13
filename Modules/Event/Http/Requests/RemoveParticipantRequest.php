<?php


namespace Modules\Event\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Modules\Event\Entities\Participant;

/**
 * Class RemoveParticipantRequest
 * @package Modules\Event\Http\Requests
 *
 * @property int $participantId
 */
class RemoveParticipantRequest extends FormRequest
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
            'participantId' => [
                'integer',
                'required',
                'exists:'.Participant::TABLE_NAME.',id',
            ]
        ];
    }
}
