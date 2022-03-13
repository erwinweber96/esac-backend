<?php


namespace Modules\Group\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Event\Entities\Event;
use Modules\Group\Entities\Group;
use Modules\Group\Entities\GroupResult;
use Modules\Group\Http\Requests\CreateGroupRequest;
use Modules\Page\Entities\Page;

/**
 * Class GroupResultController
 * @package Modules\Group\Http\Controllers
 */
class GroupResultController
{
    /**
     * @param CreateGroupRequest $request
     * @return \Illuminate\Http\JsonResponse|Group
     */
    public function createGroup(CreateGroupRequest $request)
    {
        $event = DB::table(Event::TABLE_NAME)
            ->where('id', $request->eventId)
            ->first(['page_id']);

        $page = DB::table(Page::TABLE_NAME)
            ->where('id', $event->page_id)
            ->first(['user_id']);

        if (auth()->user()->id != 15) {
            if (auth()->user()->id !== $page->user_id) {
                return response()->json([], Response::HTTP_UNAUTHORIZED);
            }
        }

        $request->validated();

        $group = new Group();

        $group->name        = $request->name;
        $group->minSize     = $request->minSize;
        $group->maxSize     = $request->maxSize;
        $group->isTypeTree  = $request->isTypeTree;
        $group->eventId     = $request->eventId;
        $group->type        = Group::TYPE_RESULT;
        $group->private     = true;

        $group->save();
        return $group;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|GroupResult
     */
    public function createResult(Request $request)
    {
        $event = DB::table(Event::TABLE_NAME)
            ->where('id', $request->input("eventId"))
            ->first(['page_id']);

        $page = DB::table(Page::TABLE_NAME)
            ->where('id', $event->page_id)
            ->first(['user_id']);

        if (auth()->user()->id != 15) {
            if (auth()->user()->id !== $page->user_id) {
                return response()->json([], Response::HTTP_UNAUTHORIZED);
            }
        }

        $groupResult = new GroupResult();

        $groupResult->groupId       = $request->input("groupId");
        $groupResult->participantId = $request->input("participantId");
        $groupResult->position      = $request->input("position");
        $groupResult->prize         = $request->input("prize");
        $groupResult->result        = $request->input("result");

        $groupResult->save();
        return $groupResult;
    }

    /**
     * @param $resultId
     * @return bool|\Illuminate\Http\JsonResponse|null
     * @throws \Exception
     */
    public function deleteResult($resultId)
    {
        /** @var GroupResult $groupResult */
        $groupResult = GroupResult::where("id", $resultId)->first();

        if (!$groupResult) {
            return response()->json([], Response::HTTP_BAD_REQUEST);
        }

        if (auth()->user()->id != 15) {
            $admin = $groupResult->group->event->page->user->id;
            if (auth()->user()->id !== $admin) {
                return response()->json([], Response::HTTP_UNAUTHORIZED);
            }
        }

        $groupResult->delete();
        return response()->json([], Response::HTTP_OK);
    }

    public function get($eventId)
    {
        $groups = DB::table(Group::TABLE_NAME)
            ->where("type", Group::TYPE_RESULT)
            ->where("event_id", $eventId)
            ->get([
                "id",
                "name"
            ]);

        foreach ($groups as $index => $group) {
            $groupResults = GroupResult::where("group_id", $group->id)
                ->with('participant')
                ->with('participant.page')
                ->with('participant.user')
                ->orderBy('position', 'asc')
                ->get();
            $groups[$index]->results = $groupResults->toArray();
        }

        return $groups;
    }
}
