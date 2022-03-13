<?php


namespace Modules\Console\Http\Controllers;


use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Console\Console\SchedulerCron;
use Modules\Console\Console\SchedulerCronTest;
use Modules\Console\Console\WeeklyTeamEvents\Handlers\CronFifthPhase;
use Modules\Console\Console\WeeklyTeamEvents\Handlers\CronFourthPhase;
use Modules\Console\Console\WeeklyTeamEvents\Handlers\CronPrepareEvent;
use Modules\Console\Console\WeeklyTeamEvents\Handlers\CronSecondPhase;
use Modules\Console\Console\WeeklyTeamEvents\Handlers\CronThirdPhase;
use Modules\Console\Entities\ConsoleAccess;
use Modules\Console\Entities\ScheduledAction;
use Modules\Console\Entities\ScheduledActionFilter;
use Modules\Console\Scheduler\Actions\CreateGroup;
use Modules\Console\Scheduler\Filters\EventPropertyFilter;
use Modules\User\Entities\User;

/**
 * Class SchedulerController
 * @package Modules\Console\Http\Controllers
 */
class SchedulerController extends Controller
{
    /**
     * @param User $user
     * @return bool
     */
    private function hasAccess(User $user)
    {
        $access = ConsoleAccess::where("user_id", $user->id)->get();

        /** @var ConsoleAccess $pass */
        foreach ($access as $pass) {
            if (!$pass->until->isPast()) {
                return true;
            }
        }

        return false;
    }

    public function getScheduledActions()
    {
        /** @var User $user */
        if (!$user = auth()->user()) {
            return response()->json([
                "message" => "Not Authorized."
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (!$this->hasAccess($user)) {
            return response()->json([
                "message" => "Not Authorized."
            ], Response::HTTP_UNAUTHORIZED);
        }

        return ScheduledAction::where("user_id", $user->id)->get();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function createGroup(Request $request)
    {
        /** @var User $user */
        if (!$user = auth()->user()) {
            return response()->json([
                "message" => "Not Authorized."
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (!$this->hasAccess($user)) {
            return response()->json([
                "message" => "Not Authorized."
            ], Response::HTTP_UNAUTHORIZED);
        }

        $scheduledAction = new ScheduledAction();

        $scheduledAction->class         = CreateGroup::class;
        $scheduledAction->name          = $request->input("name");
        $scheduledAction->cronTypeId    = $request->input("cronTypeId");
        $scheduledAction->typeId        = $request->input("typeId");
        $scheduledAction->data          = json_encode($request->input('data'));
        $scheduledAction->userId        = $user->id;

        $date = new Carbon($request->input("actionStartDate"));
        $scheduledAction->actionDateStart = $date;

        $date = new Carbon($request->input("actionEndDate"));
        $scheduledAction->actionDateEnd   = $date;

        $scheduledAction->save();

        foreach ($request->input("filters") as $filter) {
            $scheduledActionFilter = new ScheduledActionFilter();

            $scheduledActionFilter->typeId = ScheduledActionFilter::FILTER_TYPE_MOST_RECENT;
            $scheduledActionFilter->active = true;
            $scheduledActionFilter->class  = EventPropertyFilter::class;
            $scheduledActionFilter->data   = json_encode([
                "properties" => [
                    ["key" => $filter['key'], "value" => $filter['value']]
                ]
            ]);
            $scheduledActionFilter->scheduledActionId = $scheduledAction->id;

            $scheduledActionFilter->save();
        }

        return response()->json([
            "message" => "Success."
        ], Response::HTTP_OK);
    }

    public function test()
    {
        /** @var CronPrepareEvent $handler */
        $handler = app(CronPrepareEvent::class);

        $handler->handle();
    }
}
