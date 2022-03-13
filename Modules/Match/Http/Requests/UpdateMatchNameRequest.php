<?php


namespace Modules\Match\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Modules\Group\Entities\Group;
use Modules\Match\Entities\MatchModel;

/**
 * Class UpdateMatchNameRequest
 * @package Modules\Match\Http\Requests
 *
 * @property int $matchId
 * @property string $matchName
 */
class UpdateMatchNameRequest extends FormRequest
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
            'matchName' => [
                'string',
                'required',
                'min:3',
                'max:100'
            ]
        ];
    }
}
