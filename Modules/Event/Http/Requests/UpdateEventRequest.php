<?php


namespace Modules\Event\Http\Requests;


use App\Validation\Rules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Game\Entities\Game;

/**
 * Class UpdateEventRequest
 * @package Modules\Event\Http\Requests
 *
 * @property string $about
 * @property string $type
 * @property bool   $isTeamEvent
 * @property int    $gameId
 * @property bool   $isPrivate
 * @property bool   $registrationOpen
 * @property bool   $requiredGameAccount
 * @property bool   $isLineupChangeAllowed
 * @property bool   $discordRequired
 * @property bool   $pendingRegistration
 * @property string $name
 */
class UpdateEventRequest extends FormRequest
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
            'about' => [
                'string',
                'required',
            ],
            'type' => [
                'string',
                'required',
                'max:50',
                'min:3',
            ],
            'isTeamEvent' => [
                'bool',
                'required'
            ],
            'gameId' => [
                'int',
                'required',
                'exists:'.Game::TABLE_NAME.',id'
            ],
            'isPrivate' => [
                'boolean',
                'required'
            ],
            'registrationOpen' => [
                'boolean',
                'required'
            ],
            'requiredGameAccount' => [
                'boolean',
                'required'
            ],
            'isLineupChangeAllowed' => [
                'boolean',
                'required'
            ],
            'discordRequired' => [
                'boolean',
                'required'
            ],
            'pendingRegistration' => [
                'boolean',
                'required'
            ],
            'name' => [
                'string',
                'max:100',
                'min:7',
                Rule::notIn(Rules::FORBIDDEN_SLUGS),
            ],
        ];
    }
}
