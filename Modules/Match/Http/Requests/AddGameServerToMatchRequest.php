<?php


namespace Modules\Match\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Modules\Match\Entities\MatchModel;
use Modules\Match\Entities\MatchResult;

/**
 * Class AddGameServerToMatchRequest
 * @package Modules\Match\Http\Requests
 *
 * @property int    $matchId
 * @property string $serverLink
 * @property string $serverPassword
 * @property bool   $pending
 * @property string $name
 */
class AddGameServerToMatchRequest extends FormRequest
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
                'integer',
                'required',
                'exists:'.MatchModel::TABLE_NAME.',id',
            ],
            'serverLink' => [
                'string',
                'required',
                'min:3',
                'max:100'
            ],
            'serverPassword' => [
                'string',
                'max:100',
                'nullable'
            ],
            'pending' => [
                'boolean'
            ],
            'name' => [
                'string',
                'required',
                'min:3',
                'max:100'
            ]
        ];
    }
}
