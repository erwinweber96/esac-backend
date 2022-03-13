<?php

namespace Modules\Group\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Event\Entities\Event;
use Modules\Event\Entities\EventModeratorRole;
use Modules\Group\Entities\Group;
use Modules\Group\Entities\GroupProperty;
use Modules\Group\Http\Requests\CreateGroupRequest;
use Modules\Group\Http\Requests\UpdateGroupFormatsRequest;
use Modules\Group\Http\Requests\UpdateGroupNameRequest;
use Modules\Group\Http\Requests\UpdateGroupParticipantsRequest;
use Modules\Group\Jobs\BuildGroup;
use Modules\Group\Jobs\UpdateGroupFormats;
use Modules\Group\Jobs\BuildGroupForNameUpdate;
use Modules\Group\Jobs\UpdateGroupParticipants;
use Modules\Group\Repositories\GroupRepository;
use Modules\Map\Entities\MapPool;
use Modules\Match\Entities\MatchModel;
use Modules\Match\Entities\MatchResult;
use Modules\User\Entities\User;

class GroupController extends Controller
{
    /** @var GroupRepository $groupRepository */
    private $groupRepository;

    /** @var BuildGroup $buildGroup */
    private $buildGroup;

    /** @var UpdateGroupFormats $updateGroupFormats */
    private $updateGroupFormats;

    /** @var UpdateGroupParticipants $updateGroupParticipants */
    private $updateGroupParticipants;

    /** @var BuildGroupForNameUpdate $buildGroupForNameUpdate */
    private $buildGroupForNameUpdate;

    /**
     * GroupController constructor.
     * @param GroupRepository $groupRepository
     * @param BuildGroup $buildGroup
     * @param UpdateGroupFormats $updateGroupFormats
     * @param UpdateGroupParticipants $updateGroupParticipants
     * @param BuildGroupForNameUpdate $updateGroupName
     */
    public function __construct(GroupRepository $groupRepository, BuildGroup $buildGroup, UpdateGroupFormats $updateGroupFormats, UpdateGroupParticipants $updateGroupParticipants, BuildGroupForNameUpdate $updateGroupName)
    {
        $this->groupRepository = $groupRepository;
        $this->buildGroup = $buildGroup;
        $this->updateGroupFormats = $updateGroupFormats;
        $this->updateGroupParticipants = $updateGroupParticipants;
        $this->buildGroupForNameUpdate = $updateGroupName;
    }

    public function create(CreateGroupRequest $request)
    {
        $request->validated();

        /** @var User $user */
        $user = auth()->user();

        /** @var Event $event */
        $event = Event::where("id", $request->eventId)->first();

        $admin = false;
        if ($user->id == 15) {
            $admin = true;
        }

        if ($user->cannot(EventModeratorRole::CREATE_GROUP, [$event]) && !$admin) {
            return response()->json([
                "errors" => [
                    "message" => ["Not Authorized"]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        $builder = $this->buildGroup->execute($request);
        $group = $this->groupRepository->create($builder);

        return response()->json($group, Response::HTTP_OK);
    }

    public function updateFormats(UpdateGroupFormatsRequest $request)
    {
        /** @var User $user */
        $user = auth()->user();

        /** @var Group $group */
        $group = Group::where("id", $request->groupId)->first();
        $event = $group->event;

        $admin = false;
        if ($user->id == 15) {
            $admin = true;
        }

        if ($user->cannot(EventModeratorRole::EDIT_GROUP_FORMATS, [$event]) && !$admin) {
            return response()->json([
                "errors" => [
                    "message" => ["Not Authorized"]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        $request->validated();

        $job = $this->updateGroupFormats->execute($request);
        return response()->json($job, Response::HTTP_OK);
    }

    public function get($groupId)
    {
        return response()->json(
            $this->groupRepository->show($groupId)->toArray(),
            Response::HTTP_OK
        );
    }

    public function updateParticipants(UpdateGroupParticipantsRequest $request)
    {
        /** @var User $user */
        $user = auth()->user();

        /** @var Group $group */
        $group = Group::where("id", $request->groupId)->first();
        $event = $group->event;

        $admin = false;
        if ($user->id == 15) {
            $admin = true;
        }

        if ($user->cannot(EventModeratorRole::EDIT_GROUP_PARTICIPANTS, [$event]) && !$admin) {
            return response()->json([
                "errors" => [
                    "message" => ["Not Authorized"]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        $request->validated();

        $job = $this->updateGroupParticipants->execute($request);
        return response()->json($job, Response::HTTP_OK);
    }

    public function updateGroupName(UpdateGroupNameRequest $request)
    {
        $request->validated();

        /** @var User $user */
        $user = auth()->user();

        /** @var Group $group */
        $group = Group::where("id", $request->groupId)->first();
        $event = $group->event;

        $admin = false;
        if ($user->id == 15) {
            $admin = true;
        }

        if ($user->cannot(EventModeratorRole::EDIT_GROUP, [$event]) && !$admin) {
            return response()->json([
                "errors" => [
                    "message" => ["Not Authorized"]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        $group->name = $request->groupName;

        try {
            $group->save();
        } catch (\Throwable $exception) {
            return response()->json([
                "errors" => ["message" => "Something went wrong. Could not update group."]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message" => "Successfully updated group."
        ], Response::HTTP_OK);
    }

    public function delete($id)
    {
        /** @var User $user */
        $user = auth()->user();

        /** @var Group $group */
        $group = Group::where("id", $id)->first();
        $event = $group->event;

        $admin = false;
        if ($user->id == 15) {
            $admin = true;
        }

        if ($user->cannot(EventModeratorRole::DELETE_GROUP, [$event]) && !$admin) {
            return response()->json([
                "errors" => [
                    "message" => ["Not Authorized"]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $deleted = $this->groupRepository->delete($id);
        } catch (\Throwable $exception) {
            return response()->json([
                "errors" => ["message" => "Something went wrong. Could not delete group."]
            ], Response::HTTP_OK);
        }

        if (!$deleted) {
            return response()->json([
                "errors" => ["message" => "Something went wrong. Could not delete group."]
            ], Response::HTTP_OK);
        }

        return response()->json([
            "message" => "Successfully deleted group."
        ], Response::HTTP_OK);
    }

    public function getGroupParticipants(Request $request)
    {
        $groups = Group::where("id", $request->groupId)
            ->with("participants")
            ->with('participants.user')
            ->with('participants.page')
            ->with('participants.user.maniaplanet')
            ->get()
            ->map(function($group){
                return $group->participants;
            });

        if (!empty($groups->toArray())) {
            return $groups[0];
        }

        return [];
    }

    public function editor(Request $request)
    {
        $groupName = $request->input("name");
        $groupId   = $request->input("id");

        /** @var Group $group */
        $group = Group::where("id", $groupId)
            ->with('event.mapPools')
            ->first();

        /** @var User $user */
        $user = auth()->user();

        $admin = false;
        if ($user->id == 15) {
            $admin = true;
        }

        if ($user->id != $group->event->page->user->id && !$admin) {
            return response()->json([], Response::HTTP_UNAUTHORIZED);
        }

        $group->name = $groupName;
        $group->save();

        $mapPool = DB::table(MapPool::TABLE_NAME)
            ->where('event_id', $group->eventId)
            ->first(['id']);

        foreach ($request->input("matches") as $matchData) {
            if ($matchData['id'] == 0) {
                $match = new MatchModel();

                $match->mapPoolId = $mapPool->id;
                $match->date = Carbon::now();
                $match->groupId = $groupId;
            } else {
                /** @var MatchModel $match */
                $match = MatchModel::where("id", $matchData['id'])->first();
            }

            $match->name = $matchData['name'];
            $match->save();

            $participantIds = [];
            if (!isset($matchData['participants'])) {
                continue;
            }
            foreach ($matchData['participants'] as $participantData) {
                $participantIds[] = $participantData['id'];

                if (isset($participantData['total_result']) && $participantData['total_result'] != "") {
                    $matchResult = new MatchResult();

                    $matchResult->participantId = $participantData['id'];
                    $matchResult->result = $participantData['total_result'];
                    $matchResult->isTotalResult = true;
                    $matchResult->pending = false;
                    $matchResult->matchId = $match->id;

                    $matchResult->save();
                }
            }

            $match->participants()->sync($participantIds);
        }
    }

    public function getGroupProperties($groupId)
    {
        return GroupProperty::where("group_id", $groupId)->get();
    }
}
