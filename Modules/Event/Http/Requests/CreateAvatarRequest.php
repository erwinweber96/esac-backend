<?php


namespace Modules\Event\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;

class CreateAvatarRequest extends FormRequest
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
            'avatar' => [
                'image',
                'max:1024'
            ]
        ];
    }
}
