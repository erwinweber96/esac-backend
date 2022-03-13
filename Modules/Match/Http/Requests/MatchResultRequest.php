<?php


namespace Modules\Match\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Event\Entities\Participant;
use Modules\Match\Entities\MatchModel;

/**
 * Class MatchResultRequest
 * @package Modules\Match\Http\Requests
 *
 * @property string $result
 * @property int    $matchId
 * @property bool   $isTotalResult
 * @property int    $participantId
 * @property bool   $pending
 */
class MatchResultRequest extends FormRequest
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
            'result' => [
                'string',
                'required',
                'max:100'
            ],
            'matchId' => [
                'integer',
                'required',
                'exists:'.MatchModel::TABLE_NAME.',id',
            ],
            'isTotalResult' => [
                'boolean',
                'required'
            ],
            'participantId' => [
                'integer',
                'required',
                'exists:'.Participant::TABLE_NAME.',id',
            ],
            'pending' => [
                'boolean'
            ]
        ];
    }
}
