<?php


namespace Modules\Match\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Modules\Match\Entities\MatchModel;

/**
 * Class AddVodToMatchRequest
 * @package Modules\Match\Http\Requests
 *
 * @property int        $matchId
 * @property string     $url
 * @property string     $name
 * @property boolean    $pending
 */
class AddVodToMatchRequest extends FormRequest
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
            'url' => [
                'string',
                'required',
                'min:3',
                'max:100'
            ],
            'name' => [
                'string',
                'required',
                'min:3',
                'max:100'
            ],
            'pending' => [
                'boolean',
                'required'
            ]
        ];
    }
}
