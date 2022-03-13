<?php


namespace Modules\Match\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Modules\Event\Entities\Participant;
use Modules\Match\Entities\MatchModel;

/**
 * Class UpdateMatchParticipantsRequest
 * @package Modules\Match\Http\Requests
 *
 * @property int $matchId
 * @property array $participants
 */
class UpdateMatchParticipantsRequest extends FormRequest
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
            'matchId' => [
                'int',
                'required',
                'exists:'.MatchModel::TABLE_NAME.',id',
            ],
            'participants.*' => [
                'int',
                'required',
                'exists:'.Participant::TABLE_NAME.',id',
            ]
        ];
    }
}
