<?php


namespace Modules\Match\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Modules\GameServer\Entities\GameServer;
use Modules\Match\Entities\MatchModel;

/**
 * Class ApproveGameServerRequest
 * @package Modules\Match\Http\Requests
 *
 * @property int $matchId
 * @property int $gameServerId
 */
class ApproveGameServerRequest extends FormRequest
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
            'matchId' => [
                'integer',
                'required',
                'exists:'.MatchModel::TABLE_NAME.',id',
            ],
            'gameServerId' => [
                'integer',
                'required',
                'exists:'.GameServer::TABLE_NAME.',id',
            ]
        ];
    }
}
