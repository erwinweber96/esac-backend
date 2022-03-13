<?php


namespace Modules\Event\Http\Requests;


use App\Validation\Rules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Game\Entities\Game;
use Modules\Page\Entities\Page;

class CreateEventRequest extends FormRequest
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
            'name' => [
                'string',
                'required',
                'max:100',
                'min:7',
                'unique:events',
                Rule::notIn(Rules::FORBIDDEN_SLUGS),
            ],
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
            'pageId' => [
                'int',
                'required',
                'exists:'.Page::TABLE_NAME.',id',
            ],
            'isTeamEvent' => [
                'bool',
            ],
            'gameId' => [
                'int',
                'required',
                'exists:'.Game::TABLE_NAME.',id'
            ],
            'date' => [
                'string',
                'required'
            ],
            'time' => [
                'string',
                'required'
            ]
        ];
    }
}
