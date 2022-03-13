<?php


namespace Modules\Event\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Modules\Event\Entities\Event;
use Modules\User\Entities\User;

/**
 * Class WithdrawParticipantRequest
 * @package Modules\Event\Http\Requests
 *
 * @property int $userId
 * @property int $eventId
 */
class WithdrawParticipantRequest extends FormRequest
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
            'userId' => [
                'integer',
                'required',
                'exists:'.User::TABLE_NAME.',id',
            ],
            'eventId' => [
                'integer',
                'required',
                'exists:'.Event::TABLE_NAME.',id',
            ]
        ];
    }
}
