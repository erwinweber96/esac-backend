<?php


namespace Modules\Match\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Modules\Match\Entities\LiveStream;

/**
 * Class ApproveLiveStreamRequest
 * @package Modules\Match\Http\Requests
 *
 * @property int $liveStreamId
 */
class ApproveLiveStreamRequest extends FormRequest
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
            'liveStreamId' => [
                'integer',
                'required',
                'exists:'.LiveStream::TABLE_NAME.',id',
            ]
        ];
    }
}
