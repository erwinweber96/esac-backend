<?php


namespace Modules\Group\Http\Requests;


use App\Validation\Rules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Event\Entities\Event;
use Modules\Group\Entities\FormatType;
use Modules\Page\Entities\PageType;

/**
 * Class CreateFormatRequest
 * @package Modules\Group\Http\Requests
 *
 * @property string $name
 * @property int    $typeId
 * @property int    $eventId
 * @property bool   $inheritable
 * @property bool   $areResultsAdditive
 * @property bool   $isGameServerGuarded
 * @property bool   $matchModifiableByParticipants
 * @property bool   $requiresModeratorApproval
 */
class CreateFormatRequest extends FormRequest
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
                'min:3',
            ],
            'typeId' => [
                'int',
                'required',
                Rule::in(FormatType::VALUES)
            ],
            'eventId' => [
                'int',
                'required',
                'exists:'.Event::TABLE_NAME.',id',
            ],
            'inheritable' => [
                'boolean',
                'required'
            ],
            'areResultsAdditive' => [
                'boolean',
                'required'
            ],
            'isGameServerGuarded' => [
                'boolean',
                'required'
            ],
            'matchModifiableByParticipants' => [
                'boolean',
                'required'
            ],
            'requiresModeratorApproval' => [
                'boolean',
                'required'
            ],
            'description' => [
                'string',
                'min:5',
                'max:1000',
                'nullable'
            ]
        ];
    }
}
