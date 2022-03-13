<?php


namespace Modules\Map\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Modules\Map\Entities\MapPool;

/**
 * Class UpdateMapPoolNameRequest
 * @package Modules\Map\Http\Requests
 *
 * @property int $mapPoolId
 * @property string $mapPoolName
 */
class UpdateMapPoolNameRequest extends FormRequest
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
            'mapPoolId' => [
                'int',
                'required',
                'exists:'.MapPool::TABLE_NAME.',id',
            ],
            'mapPoolName' => [
                'string',
                'required',
                'min:3',
                'max:100'
            ]
        ];
    }
}
