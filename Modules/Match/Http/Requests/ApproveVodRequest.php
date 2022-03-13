<?php


namespace Modules\Match\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Modules\Match\Entities\Vod;

/**
 * Class ApproveVodRequest
 * @package Modules\Match\Http\Requests
 *
 * @property int $vodId
 */
class ApproveVodRequest extends FormRequest
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
            'vodId' => [
                'integer',
                'required',
                'exists:'.Vod::TABLE_NAME.',id',
            ]
        ];
    }
}
