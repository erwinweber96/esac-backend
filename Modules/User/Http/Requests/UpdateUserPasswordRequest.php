<?php


namespace Modules\User\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UpdateUserPasswordRequest
 * @package Modules\User\Http\Requests
 *
 * @property string $password
 * @property string $confirmPassword
 */
class UpdateUserPasswordRequest extends FormRequest
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
            'password' => 'required|min:6|required_with:confirmPassword|same:confirmPassword',
            'confirmPassword' => 'required|min:6',
        ];
    }
}
