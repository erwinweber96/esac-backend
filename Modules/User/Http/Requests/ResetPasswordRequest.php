<?php


namespace Modules\User\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;

/**
 * Class ResetPasswordRequest
 * @package Modules\User\Http\Requests
 *
 * @property string $password
 * @property string $confirmPassword
 * @property string $token
 */
class ResetPasswordRequest extends FormRequest
{
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
            'token' => 'required'
        ];
    }
}
