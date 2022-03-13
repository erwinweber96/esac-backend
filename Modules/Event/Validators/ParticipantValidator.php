<?php


namespace Modules\Event\Validators;


use Illuminate\Http\Request;

interface ParticipantValidator
{
    /**
     * @param Request $request
     * @throws \Exception
     */
    public function validate(Request $request): void;
}
