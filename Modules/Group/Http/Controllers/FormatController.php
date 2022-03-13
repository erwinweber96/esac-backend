<?php


namespace Modules\Group\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Event\Entities\Event;
use Modules\Event\Entities\EventModeratorRole;
use Modules\Group\Builders\FormatBuilder;
use Modules\Group\Entities\Format;
use Modules\Group\Entities\MatchSetting;
use Modules\Group\Http\Requests\CreateFormatRequest;
use Modules\Group\Http\Requests\UpdateFormatNameRequest;
use Modules\Group\Jobs\BuildFormatForNameUpdate;
use Modules\Group\Repositories\FormatRepository;
use Modules\User\Entities\User;

class FormatController extends Controller
{
    /** @var FormatRepository $formatRepository */
    private $formatRepository;

    /** @var BuildFormatForNameUpdate $buildFormatForNameUpdate */
    private $buildFormatForNameUpdate;

    /**
     * FormatController constructor.
     * @param FormatRepository $formatRepository
     * @param BuildFormatForNameUpdate $buildFormatForNameUpdate
     */
    public function __construct(FormatRepository $formatRepository, BuildFormatForNameUpdate $buildFormatForNameUpdate)
    {
        $this->formatRepository = $formatRepository;
        $this->buildFormatForNameUpdate = $buildFormatForNameUpdate;
    }

    public function create(CreateFormatRequest $request)
    {
        $request->validated();

        /** @var User $user */
        $user = auth()->user();

        /** @var Event $event */
        $event = Event::where("id", $request->eventId)->first();

        if ($user->cannot(EventModeratorRole::CREATE_FORMAT, [$event])) {
            return response()->json([
                "errors" => [
                    "message" => ["Not Authorized"]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        /** @var FormatBuilder $builder */
        $builder = (new FormatBuilder())->prepare();

        $builder
            ->setName($request->input("name"))
            ->setTypeId($request->input("typeId"))
            ->setAreResultsAdditive($request->input("areResultsAdditive"))
            ->setInheritable($request->input("inheritable"))
            ->setMatchModifiableByParticipants($request->input("matchModifiableByParticipants"))
            ->setRequiresModeratorApproval($request->input("requiresModeratorApproval"))
            ->setIsGameServerGuarded($request->input("isGameServerGuarded"))
            ->setEventId($request->input("eventId"))
            ->setDescription($request->input("description"));

        $format = $this->formatRepository->create($builder);
        return response()->json($format, Response::HTTP_OK);
    }

    public function updateFormatName(UpdateFormatNameRequest $request)
    {
        $request->validated();

        /** @var User $user */
        $user = auth()->user();

        /** @var Format $format */
        $format = Format::where("id", $request->formatId)->first();

        if ($user->cannot(EventModeratorRole::EDIT_FORMAT, [$format->event])) {
            return response()->json([
                "errors" => [
                    "message" => ["Not Authorized"]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        $builder = $this->buildFormatForNameUpdate->execute($request);
        $updated = $this->formatRepository->update($builder, $request->formatId);

        if (!$updated) {
            return response()->json([
                "errors" => ["message" => "Something went wrong. Could not update Format."]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message" => "Successfully updated Format."
        ], Response::HTTP_OK);
    }

    public function delete($id)
    {
        /** @var User $user */
        $user = auth()->user();

        /** @var Format $format */
        $format = Format::where("id", $id)->first();

        if ($user->cannot(EventModeratorRole::DELETE_FORMAT, [$format->event])) {
            return response()->json([
                "errors" => [
                    "message" => ["Not Authorized"]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $deleted = $this->formatRepository->delete($id);
        } catch (\Throwable $exception) {
            return response()->json([
                "errors" => ["message" => "Something went wrong. Could not delete Format."]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if (!$deleted) {
            return response()->json([
                "errors" => ["message" => "Something went wrong. Could not delete Format."]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message" => "Successfully deleted Format."
        ], Response::HTTP_OK);
    }

    public function addMatchSettings(Request $request)
    {
        $formatId = $request->input("formatId");
        $matchSettings = $request->input("matchSettings");

        foreach ($matchSettings as $matchSetting) {
            if (!isset($matchSetting['key'])) {
                continue;
            }

            if (isset($matchSetting['id'])) {
                MatchSetting::where("id", $matchSetting['id'])->update([
                    "key" => $matchSetting['key'],
                    "value" => $matchSetting['value']
                ]);
                continue;
            }

            MatchSetting::create([
                "key" => $matchSetting['key'],
                "value" => $matchSetting['value'],
                "format_id" => $formatId
            ]);
        }
    }
}
