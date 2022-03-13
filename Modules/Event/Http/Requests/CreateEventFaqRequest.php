<?php


namespace Modules\Event\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Modules\Event\Entities\Event;

/**
 * Class CreateEventFaqRequest
 * @package Modules\Event\Http\Requests
 *
 * @property string $question
 * @property string $answer
 * @property int    $eventId
 */
class CreateEventFaqRequest extends FormRequest
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
            'question' => [
                'string',
                'required',
                'max:100',
                'min:3'
            ],
            'answer' => [
                'string',
                'required',
                'max:1000',
                'min:2'
            ],
            'eventId' => [
                'int',
                'required',
                'exists:'.Event::TABLE_NAME.',id',
            ]
        ];
    }
}
