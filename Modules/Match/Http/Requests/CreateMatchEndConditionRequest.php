<?php


namespace Modules\Match\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Modules\Group\Entities\Format;

/**
 * Class CreateMatchEndConditionRequest
 * @package Modules\Match\Http\Requests
 *
 * @property integer $formatId
 * @property integer $minMapsPlayed
 * @property integer $maxMapsPlayed
 * @property integer $pointsReached
 * @property integer $numberOfPlayersWithPointsReached
 * @property integer $id
 */
class CreateMatchEndConditionRequest extends FormRequest
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
            'formatId' => [
                'integer',
                'required',
                'exists:'.Format::TABLE_NAME.',id',
            ],
            'minMapsPlayed' => [
                'integer',
                'nullable'
            ],
            'maxMapsPlayed' => [
                'integer',
                'nullable'
            ],
            'pointsReached' => [
                'integer',
                'nullable'
            ],
            'numberOfPlayersWithPointsReached' => [
                'integer',
                'nullable'
            ]
        ];
    }
}
