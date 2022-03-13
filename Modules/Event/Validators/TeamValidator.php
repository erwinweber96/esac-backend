<?php


namespace Modules\Event\Validators;


use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Page\Entities\Page;
use Modules\Page\Repositories\PageRepository;

class TeamValidator implements ParticipantValidator
{
    /** @var PageRepository $pageRepository */
    private $pageRepository;

    /**
     * TeamValidator constructor.
     * @param PageRepository $pageRepository
     */
    public function __construct(PageRepository $pageRepository)
    {
        $this->pageRepository = $pageRepository;
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function validate(Request $request): void
    {
        /** @var Page $page */
        $page = $this->pageRepository->show($request->input("participantId"));

        if (!$page) {
            throw new \Exception(
                "Page with given id could not be found",
                Response::HTTP_BAD_REQUEST
            );
        }

        if ($page->user->id != auth()->user()->id) {
            response()->json([
                "errors" => [
                    "message" => ["Not Authorized"]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }
    }
}
