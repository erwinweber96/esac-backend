<?php


namespace Modules\Event\Http\Controllers;


use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Event\Entities\Event;
use Modules\Event\Entities\EventFaq;
use Modules\Event\Entities\EventModeratorRole;
use Modules\Event\Http\Requests\CreateEventFaqRequest;
use Modules\Event\Jobs\BuildEventFaq;
use Modules\Event\Repositories\EventFaqRepository;
use Modules\User\Entities\User;

class EventFaqController extends Controller
{
    /** @var BuildEventFaq $buildFaq */
    private $buildFaq;

    /** @var EventFaqRepository $faqRepository */
    private $faqRepository;

    /**
     * EventFaqController constructor.
     * @param BuildEventFaq $buildFaq
     * @param EventFaqRepository $faqRepository
     */
    public function __construct(BuildEventFaq $buildFaq, EventFaqRepository $faqRepository)
    {
        $this->buildFaq = $buildFaq;
        $this->faqRepository = $faqRepository;
    }

    public function create(CreateEventFaqRequest $request)
    {
        $request->validated();

        /** @var User $user */
        $user = auth()->user();

        /** @var Event $event */
        $event = Event::where("id", $request->eventId)->first();

        if ($user->cannot(EventModeratorRole::CREATE_FAQ, [$event])) {
            return response()->json([
                "errors" => [
                    "message" => ["Not Authorized"]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }



        $builder = $this->buildFaq->execute($request);
        $faq     = $this->faqRepository->create($builder);

        return response()->json($faq, Response::HTTP_OK);
    }

    /**
     * @param $id
     * @return bool|\Illuminate\Http\JsonResponse|null
     * @throws \Exception
     */
    public function delete($id)
    {
        /** @var User $user */
        $user = auth()->user();

        /** @var EventFaq $faq */
        $faq = EventFaq::where("id", $id)->first();

        if ($user->cannot(EventModeratorRole::CREATE_FAQ, [$faq->event])) {
            return response()->json([
                "errors" => [
                    "message" => ["Not Authorized"]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $faq->delete();
    }
}
