<?php


namespace Modules\User\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;

/**
 * Class CreateUserDiscordRequest
 * @package Modules\User\Http\Requests
 *
 * @property string $nickname
 * @property int $id
 */
class CreateUserDiscordRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'nickname' => 'required|min:1|max:32|string',
            'id' => 'required|min:4|string',
        ];
    }
}
