<?php


namespace Modules\User\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;

class UserRegistrationRequest extends FormRequest
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
            'email' => 'required|email|unique:users',
            'nickname' => 'required|string|max:50',
            'password' => 'required|min:6|required_with:confirmPassword|same:confirmPassword',
            'confirmPassword' => 'required|min:6',
            'nationality' => 'required|string|max:2'
        ];
    }
}
