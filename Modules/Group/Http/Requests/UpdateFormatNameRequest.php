<?php


namespace Modules\Group\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Modules\Group\Entities\Format;

/**
 * Class UpdateFormatNameRequest
 * @package Modules\Group\Http\Requests
 *
 * @property int $formatId
 * @property string $formatName
 */
class UpdateFormatNameRequest extends FormRequest
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
                'int',
                'required',
                'exists:'.Format::TABLE_NAME.',id',
            ],
            'formatName' => [
                'string',
                'required',
                'min:3',
                'max:100'
            ]
        ];
    }
}
