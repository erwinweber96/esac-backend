<?php


namespace Modules\Event\Validators;


use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\User\Repositories\UserRepository;

class PlayerValidator implements ParticipantValidator
{
    /** @var UserRepository $userRepository */
    private $userRepository;

    /**
     * PlayerValidator constructor.
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function validate(Request $request): void
    {
        $user = $this->userRepository->show($request->input("participantId"));

        if (!$user) {
            throw new \Exception(
                "User with given id could not be found.",
                Response::HTTP_BAD_REQUEST
            );
        }
    }
}
