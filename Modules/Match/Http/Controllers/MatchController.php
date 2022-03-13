<?php

namespace Modules\Match\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Console\Entities\DedicatedController;
use Modules\Console\Entities\DedicatedControllerProperty;
use Modules\Event\Entities\Event;
use Modules\Event\Entities\EventModeratorRole;
use Modules\Event\Entities\EventStatus;
use Modules\GameServer\Entities\GameServer;
use Modules\GameServer\Repositories\GameServerRepository;
use Modules\Group\Entities\Group;
use Modules\Link\Builders\LinkBuilder;
use Modules\Link\Builders\LiveStreamBuilder;
use Modules\Link\Entities\Link;
use Modules\Link\Jobs\BuildEmbedLinkFromTwitchChannel;
use Modules\Link\Repositories\LinkRepository;
use Modules\Link\Repositories\LiveStreamRepository;
use Modules\Match\Builders\VodBuilder;
use Modules\Match\Entities\LiveStream;
use Modules\Match\Entities\MatchModel;
use Modules\Match\Entities\MatchResult;
use Modules\Match\Entities\Vod;
use Modules\Match\Events\MatchStatusUpdated;
use Modules\Match\Http\Requests\AddGameServerToMatchRequest;
use Modules\Match\Http\Requests\AddTwitchChannelToMatchRequest;
use Modules\Match\Http\Requests\AddVodToMatchRequest;
use Modules\Match\Http\Requests\ApproveGameServerRequest;
use Modules\Match\Http\Requests\ApproveLiveStreamRequest;
use Modules\Match\Http\Requests\ApproveResultRequest;
use Modules\Match\Http\Requests\ApproveVodRequest;
use Modules\Match\Http\Requests\CreateMatchRequest;
use Modules\Match\Http\Requests\MatchMapResultRequest;
use Modules\Match\Http\Requests\MatchResultRequest;
use Modules\Match\Http\Requests\UpdateMatchFormatsRequest;
use Modules\Match\Http\Requests\UpdateMatchNameRequest;
use Modules\Match\Http\Requests\UpdateMatchParticipantsRequest;
use Modules\Match\Jobs\BuildGameServerFromAddToMatchRequest;
use Modules\Match\Jobs\BuildMatch;
use Modules\Match\Jobs\BuildMatchForNameUpdate;
use Modules\Match\Jobs\BuildMatchMapResult;
use Modules\Match\Jobs\BuildMatchResult;
use Modules\Match\Jobs\SendMatchStatusUpdatedEventToDiscordWebhook;
use Modules\Match\Jobs\UpdateMatchFormats;
use Modules\Match\Jobs\UpdateMatchParticipants;
use Modules\Match\Repositories\MatchRepository;
use Modules\Match\Repositories\MatchResultRepository;
use Modules\Match\Repositories\VodRepository;
use Modules\User\Entities\User;
use Modules\User\Entities\UserNotification;
use Sentry\Laravel\Facade;
use function Sentry\captureMessage;

/**
 * Class MatchController
 * @package Modules\Match\Http\Controllers
 */
class MatchController extends Controller
{
    /** @var MatchRepository $matchRepository */
    private $matchRepository;

    /** @var BuildMatch $buildMatch */
    private $buildMatch;

    /** @var UpdateMatchParticipants $updateMatchParticipants */
    private $updateMatchParticipants;

    /** @var UpdateMatchFormats $updateMatchFormats */
    private $updateMatchFormats;

    /** @var BuildMatchResult $buildMatchResult */
    private $buildMatchResult;

    /** @var MatchResultRepository $matchResultRepository */
    private $matchResultRepository;

    /** @var BuildGameServerFromAddToMatchRequest $buildGameServer */
    private $buildGameServer;

    /** @var GameServerRepository $gameServerRepository */
    private $gameServerRepository;

    /** @var BuildEmbedLinkFromTwitchChannel $buildEmbedLinkFromTwitchChannel */
    private $buildEmbedLinkFromTwitchChannel;

    /** @var LinkRepository */
    private $linkRepository;

    /** @var LiveStreamRepository $liveStreamRepository */
    private $liveStreamRepository;

    /** @var LiveStreamBuilder $liveStreamBuilder */
    private $liveStreamBuilder;

    /** @var BuildMatchForNameUpdate $buildMatchForNameUpdate */
    private $buildMatchForNameUpdate;

    /** @var VodRepository $vodRepository */
    private $vodRepository;

    /** @var BuildMatchMapResult $buildMatchMapResult */
    private $buildMatchMapResult;

    /**
     * @param MatchRepository $matchRepository
     * @param BuildMatch $buildMatch
     * @param UpdateMatchParticipants $updateMatchParticipants
     * @param UpdateMatchFormats $updateMatchFormats
     * @param BuildMatchResult $buildMatchResult
     * @param MatchResultRepository $matchResultRepository
     * @param BuildGameServerFromAddToMatchRequest $buildGameServer
     * @param GameServerRepository $gameServerRepository
     * @param BuildEmbedLinkFromTwitchChannel $buildEmbedLinkFromTwitchChannel
     * @param LinkRepository $linkRepository
     * @param LiveStreamRepository $liveStreamRepository
     * @param LiveStreamBuilder $liveStreamBuilder
     * @param BuildMatchForNameUpdate $buildMatchForNameUpdate
     * @param VodRepository $vodRepository
     * @param BuildMatchMapResult $buildMatchMapResult
     */
    public function __construct(MatchRepository $matchRepository, BuildMatch $buildMatch, UpdateMatchParticipants $updateMatchParticipants, UpdateMatchFormats $updateMatchFormats, BuildMatchResult $buildMatchResult, MatchResultRepository $matchResultRepository, BuildGameServerFromAddToMatchRequest $buildGameServer, GameServerRepository $gameServerRepository, BuildEmbedLinkFromTwitchChannel $buildEmbedLinkFromTwitchChannel, LinkRepository $linkRepository, LiveStreamRepository $liveStreamRepository, LiveStreamBuilder $liveStreamBuilder, BuildMatchForNameUpdate $buildMatchForNameUpdate, VodRepository $vodRepository, BuildMatchMapResult $buildMatchMapResult)
    {
        $this->matchRepository = $matchRepository;
        $this->buildMatch = $buildMatch;
        $this->updateMatchParticipants = $updateMatchParticipants;
        $this->updateMatchFormats = $updateMatchFormats;
        $this->buildMatchResult = $buildMatchResult;
        $this->matchResultRepository = $matchResultRepository;
        $this->buildGameServer = $buildGameServer;
        $this->gameServerRepository = $gameServerRepository;
        $this->buildEmbedLinkFromTwitchChannel = $buildEmbedLinkFromTwitchChannel;
        $this->linkRepository = $linkRepository;
        $this->liveStreamRepository = $liveStreamRepository;
        $this->liveStreamBuilder = $liveStreamBuilder;
        $this->buildMatchForNameUpdate = $buildMatchForNameUpdate;
        $this->vodRepository = $vodRepository;
        $this->buildMatchMapResult = $buildMatchMapResult;
    }


    /**
     * @param CreateMatchRequest $request
     * @return JsonResponse
     */
    public function create(CreateMatchRequest $request)
    {
        $request->validated();

        /** @var User $user */
        $user = auth()->user();

        /** @var Group $group */
        $group = Group::where("id", $request->groupId)->first();

        $admin = false;
        if ($user->id == 15) {
            $admin = true;
        }

        if ($user->cannot(EventModeratorRole::CREATE_MATCH, [$group->event]) && !$admin) {
            return response()->json([
                "errors" => [
                    "message" => ["Not Authorized"]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        $builder = $this->buildMatch->execute($request);
        $match = $this->matchRepository->create($builder);

        return response()->json($match, Response::HTTP_OK);
    }

    /**
     * @param $matchId
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function update($matchId, Request $request)
    {
        /** @var MatchModel $match */
        $match = MatchModel::where("id", $matchId)->first();

        /** @var User $user */
        $user = auth()->user();

        $admin = false;
        if ($user->id == 15) {
            $admin = true;
        }

        if ($user->id != $match->group->event->page->user->id && !$admin) {
            return response()->json([
                "errors" => [
                    "message" => ["Not Authorized"]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        $match->name        = $request->input("name");
        $match->date        = new Carbon($request->input("date"). " " . $request->input("time"));
        $match->mapPoolId   = $request->input("mapPoolId");

        $match->save();
        return response()->json(["message" => "Success."], Response::HTTP_OK);
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function get($id)
    {
        /** @var MatchModel $match */
        $match = $this->matchRepository->show($id);
        $data = $match->toArray();

        $data['map_pool']['mxData'] = [];

        if ($mapPool = $match->mapPool()->first()) {
            if ($mxData = $mapPool->mxData) {
                $data['map_pool']['mxData'] = $mxData->toArray();
            }
        }

        return response()->json($data, Response::HTTP_OK);
    }

    /**
     * @param UpdateMatchParticipantsRequest $request
     * @return JsonResponse
     */
    public function updateParticipants(UpdateMatchParticipantsRequest $request)
    {
        $request->validated();

        /** @var User $user */
        $user = auth()->user();

        /** @var MatchModel $match */
        $match = MatchModel::where("id", $request->matchId)->first();

        $admin = false;
        if ($user->id == 15) {
            $admin = true;
        }

        if ($user->cannot(EventModeratorRole::EDIT_MATCH_PARTICIPANTS, [$match->group->event]) && !$admin) {
            return response()->json([
                "errors" => [
                    "message" => ["Not Authorized"]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        $match = $this->updateMatchParticipants->execute($request);
        return response()->json($match, Response::HTTP_OK);
    }

    /**
     * @param UpdateMatchFormatsRequest $request
     * @return JsonResponse
     */
    public function updateFormats(UpdateMatchFormatsRequest $request)
    {
        $request->validated();

        /** @var User $user */
        $user = auth()->user();

        /** @var MatchModel $match */
        $match = MatchModel::where("id", $request->matchId)->first();

        $admin = false;
        if ($user->id == 15) {
            $admin = true;
        }

        if ($user->cannot(EventModeratorRole::EDIT_MATCH_FORMATS, [$match->group->event]) && !$admin) {
            return response()->json([
                "errors" => [
                    "message" => ["Not Authorized"]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        $match = $this->updateMatchFormats->execute($request);
        return response()->json($match, Response::HTTP_OK);
    }

    public function createMapResult(MatchMapResultRequest $request)
    {
        $request->validated();

        /** @var User $user */
        $user = auth()->user();

        /** @var MatchModel $match */
        $match = MatchModel::where("id", $request->matchId)->first();

        $admin = false;
        if ($user->id == 15) {
            $admin = true;
        }

        if ($request->pending == false) {
            if ($user->cannot(EventModeratorRole::ADD_TOTAL_RESULT, [$match->group->event]) && !$admin) {
                return response()->json([
                    "errors" => [
                        "message" => ["Not Authorized"]
                    ]
                ], Response::HTTP_UNAUTHORIZED);
            }
        }

        $builder     = $this->buildMatchMapResult->execute($request);
        $matchResult = $this->matchResultRepository->create($builder);

        return response()->json($matchResult, Response::HTTP_OK);
    }

    public function createResult(MatchResultRequest $request)
    {
        $request->validated();

        /** @var User $user */
        $user = auth()->user();

        /** @var MatchModel $match */
        $match = MatchModel::where("id", $request->matchId)->first();

        $admin = false;
        if ($user->id == 15) {
            $admin = true;
        }

        if ($request->pending == false) {
            if ($user->cannot(EventModeratorRole::ADD_TOTAL_RESULT, [$match->group->event]) && !$admin) {
                return response()->json([
                    "errors" => [
                        "message" => ["Not Authorized"]
                    ]
                ], Response::HTTP_UNAUTHORIZED);
            }
        }

        $builder     = $this->buildMatchResult->execute($request);
        $matchResult = $this->matchResultRepository->create($builder);

        return response()->json($matchResult, Response::HTTP_OK);
    }

    public function updateResult(MatchResultRequest $request)
    {
        //TODO: function not used? -> delete

        $request->validated();

        $builder     = $this->buildMatchResult->execute($request);
        $matchResult = $this->matchResultRepository->update($builder, $request->input("id"));

        return response()->json($matchResult, Response::HTTP_OK);
    }

    public function deleteResult($id)
    {
        /** @var User $user */
        $user = auth()->user();

        /** @var MatchResult $result */
        $result = MatchResult::where("id", $id)->first();

        $admin = false;
        if ($user->id == 15) {
            $admin = true;
        }

        if ($user->cannot(EventModeratorRole::MODERATE_RESULTS, [$result->match->group->event]) && !$admin) {
            return response()->json([
                "errors" => [
                    "message" => ["Not Authorized"]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $matchResult = $this->matchResultRepository->delete($id);
        } catch (\Exception $exception) {
            return response()->json([
                "message" => "Something went wrong. Could not delete result."
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message" => "Successfully deleted result."
        ], Response::HTTP_OK);
    }

    public function approveResult(ApproveResultRequest $request)
    {
        $request->validated();

        /** @var User $user */
        $user = auth()->user();

        /** @var MatchResult $result */
        $result = MatchResult::where("id", $request->id)->first();

        $admin = false;
        if ($user->id == 15) {
            $admin = true;
        }

        if ($user->cannot(EventModeratorRole::MODERATE_RESULTS, [$result->match->group->event]) && !$admin) {
            return response()->json([
                "errors" => [
                    "message" => ["Not Authorized"]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        $approved = $this->matchResultRepository->approve($request->input("id"));

        if (!$approved) {
            return response()->json([
                "message" => "Something went wrong. Could not approve result."
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message" => "Successfully approved result."
        ], Response::HTTP_OK);
    }

    public function addGameServer(AddGameServerToMatchRequest $request)
    {
        captureMessage(json_encode($request->toArray()));

        $request->validated();

        /** @var User $user */
        $user = auth()->user();

        /** @var MatchModel $result */
        $match = MatchModel::where("id", $request->matchId)->first();

        $admin = false;
        if ($user->id == 15) {
            $admin = true;
        }

        if ($request->pending == false) {
            if ($user->cannot(EventModeratorRole::ADD_GAME_SERVER, [$match->group->event]) && !$admin) {
                return response()->json([
                    "errors" => [
                        "message" => ["Not Authorized"]
                    ]
                ], Response::HTTP_UNAUTHORIZED);
            }
        }

        $builder = $this->buildGameServer->execute($request);

//        try {
            /** @var GameServer $gameServer */
            $gameServer = $this->gameServerRepository->create($builder);

            /** @var MatchModel $match */
            $match = $this->matchRepository->show($request->input("matchId"));

            $added = $this->matchRepository->addGameServer($match, $gameServer);
//        } catch (\Throwable $exception) {
//            return response()->json([
//                "message" => "Something went wrong. Could not add game server."
//            ], Response::HTTP_INTERNAL_SERVER_ERROR);
//        }

        $added->setAppends([]);
        return response()->json($added, Response::HTTP_OK);
    }

    public function approveGameServer(ApproveGameServerRequest $request)
    {
        $request->validated();

        /** @var User $user */
        $user = auth()->user();

        /** @var MatchModel $result */
        $match = MatchModel::where("id", $request->matchId)->first();

        $admin = false;
        if ($user->id == 15) {
            $admin = true;
        }

        if ($user->cannot(EventModeratorRole::MODERATE_GAME_SERVERS, [$match->group->event]) && !$admin) {
            return response()->json([
                "errors" => [
                    "message" => ["Not Authorized"]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        $approved = $this->gameServerRepository
            ->approveGameServer($request->input("gameServerId"));

        if (!$approved) {
            return response()->json([
                "message" => "Something went wrong. Could not approve game server."
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message" => "Successfully approved game server."
        ], Response::HTTP_OK);
    }

    public function removeGameServer($matchId, $serverId)
    {
        /** @var User $user */
        $user = auth()->user();

        /** @var MatchModel $result */
        $match = MatchModel::where("id", $matchId)->first();

        $admin = false;
        if ($user->id == 15) {
            $admin = true;
        }

        if ($user->cannot(EventModeratorRole::MODERATE_GAME_SERVERS, [$match->group->event]) && !$admin) {
            return response()->json([
                "errors" => [
                    "message" => ["Not Authorized"]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        /** @var MatchModel $match */
        $match = $this->matchRepository->show($matchId);

        $this->matchRepository->removeGameServer($match, $serverId);

        try {
            $this->gameServerRepository->delete($serverId);
        } catch (\Throwable $exception) {
            return response()->json([
                "message" => "Something went wrong. Could not remove game server."
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message" => "Successfully removed game server."
        ], Response::HTTP_OK);
    }

    public function addLiveStream(AddTwitchChannelToMatchRequest $request)
    {
        $request->validated();

        /** @var User $user */
        $user = auth()->user();

        /** @var MatchModel $result */
        $match = MatchModel::where("id", $request->matchId)->first();

        $admin = false;
        if ($user->id == 15) {
            $admin = true;
        }

        if ($request->pending == false) {
            if ($user->cannot(EventModeratorRole::ADD_LIVE_STREAM, [$match->group->event]) && !$admin) {
                return response()->json([
                    "errors" => [
                        "message" => ["Not Authorized"]
                    ]
                ], Response::HTTP_UNAUTHORIZED);
            }
        }

        $linkBuilder = $this->buildEmbedLinkFromTwitchChannel->execute($request);

        /** @var Link $link */
        $link = $this->linkRepository->create($linkBuilder);

        $liveStreamBuilder = $this->liveStreamBuilder
            ->setMatchId($request->input("matchId"))
            ->setLinkId($link->id);

        $liveStream = $this->liveStreamRepository->create($liveStreamBuilder);

        return response()->json($liveStream, Response::HTTP_OK);
    }

    public function approveLiveStream(ApproveLiveStreamRequest $request)
    {
        $request->validated();

        /** @var User $user */
        $user = auth()->user();

        /** @var LiveStream $liveStream */
        $liveStream = LiveStream::where("id", $request->liveStreamId)->first();

        $admin = false;
        if ($user->id == 15) {
            $admin = true;
        }

        if ($user->cannot(EventModeratorRole::MODERATE_LIVE_STREAMS, [$liveStream->match->group->event]) && !$admin) {
            return response()->json([
                "errors" => [
                    "message" => ["Not Authorized"]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        $this->liveStreamRepository->approveLiveStream($request->input("liveStreamId"));
    }

    public function removeLiveStream($streamId)
    {
        /** @var User $user */
        $user = auth()->user();

        /** @var LiveStream $liveStream */
        $liveStream = LiveStream::where("id", $streamId)->first();

        $admin = false;
        if ($user->id == 15) {
            $admin = true;
        }

        if ($user->cannot(EventModeratorRole::MODERATE_LIVE_STREAMS, [$liveStream->match->group->event]) && !$admin) {
            return response()->json([
                "errors" => [
                    "message" => ["Not Authorized"]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $deleted = $this->liveStreamRepository->delete($streamId);
        } catch (\Exception $exception) {
            return response()->json([
                "message" => "Something went wrong. Could not remove Live Stream."
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message" => "Successfully removed Live Stream."
        ], Response::HTTP_OK);
    }

    public function updateMatchName(UpdateMatchNameRequest $request)
    {
        $request->validated();

        /** @var User $user */
        $user = auth()->user();

        /** @var MatchModel $result */
        $match = MatchModel::where("id", $request->matchId)->first();

        $admin = false;
        if ($user->id == 15) {
            $admin = true;
        }

        if ($user->cannot(EventModeratorRole::EDIT_MATCH, [$match->group->event]) && !$admin) {
            return response()->json([
                "errors" => [
                    "message" => ["Not Authorized"]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        $builder = $this->buildMatchForNameUpdate->execute($request);
        $updated = $this->matchRepository->update($builder, $request->matchId);

        if (!$updated) {
            return response()->json([
                "errors" => ["message" => "Something went wrong. Could not update match."]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message" => "Successfully updated match."
        ], Response::HTTP_OK);
    }

    public function delete($id)
    {
        /** @var User $user */
        $user = auth()->user();

        /** @var MatchModel $result */
        $match = MatchModel::where("id", $id)->first();

        $admin = false;
        if ($user->id == 15) {
            $admin = true;
        }

        if ($user->cannot(EventModeratorRole::DELETE_MATCH, [$match->group->event]) && !$admin) {
            return response()->json([
                "errors" => [
                    "message" => ["Not Authorized"]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $deleted = $this->matchRepository->delete($id);
        } catch (\Throwable $exception) {
            return response()->json([
                "errors" => ["message" => "Something went wrong. Could not delete match."]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if (!$deleted) {
            return response()->json([
                "errors" => ["message" => "Something went wrong. Could not delete match."]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message" => "Successfully deleted match."
        ], Response::HTTP_OK);
    }

    public function updateStatus($matchId, $statusId)
    {
        /** @var User $user */
        $user = auth()->user();

        /** @var MatchModel $result */
        $match = MatchModel::where("id", $matchId)->first();

        $admin = false;
        if ($user->id == 15) {
            $admin = true;
        }

        if ($user->cannot(EventModeratorRole::EDIT_MATCH, [$match->group->event]) && !$admin) {
            return response()->json([
                "errors" => [
                    "message" => ["Not Authorized"]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        //TODO: validate statusId in Match::ALL_STATUS

        /** @var MatchModel $match */
        $match = MatchModel::where("id", $matchId)->first();
        $match->update([
            "status_id" => $statusId
        ]);

        if (!$match) {
            return response()->json([
                "errors" => ["message" => "Something went wrong. Could not update match."]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if ($statusId == MatchModel::STATUS_LIVE) {
            foreach ($match->participants as $participant) {
                if (!$participant->userId) {
                    continue;
                }

                $notification = new UserNotification();

                $notification->userId   = $participant->user->id;
                $notification->title    = $match->group->event->name;
                $notification->message  = $match->name . " is now LIVE!";
                $notification->variant  = UserNotification::DANGER_VARIANT;
                $notification->url      = "/events/".$match->group->event->slug."/matches/".$match->id;

                $notification->save();
            }
        }

        SendMatchStatusUpdatedEventToDiscordWebhook::dispatch($match);

        return response()->json([
            "message" => "Successfully updated match."
        ], Response::HTTP_OK);
    }

    public function addVod(AddVodToMatchRequest $request)
    {
        $request->validated();

        if ($request->pending == false) {
            /** @var User $user */
            $user = auth()->user();

            /** @var MatchModel $result */
            $match = MatchModel::where("id", $request->matchId)->first();

            $admin = false;
            if ($user->id == 15) {
                $admin = true;
            }

            if ($user->cannot(EventModeratorRole::ADD_VOD, [$match->group->event]) && !$admin) {
                return response()->json([
                    "errors" => [
                        "message" => ["Not Authorized"]
                    ]
                ], Response::HTTP_UNAUTHORIZED);
            }
        }

        /** @var LinkBuilder $linkBuilder */
        $linkBuilder = (new LinkBuilder())->prepare();

        $builtLink = $linkBuilder->setName($request->name)
            ->setPending($request->pending)
            ->setUrl($request->url);

        /** @var Link $link */
        $link = $this->linkRepository->create($builtLink);

        /** @var VodBuilder $vodBuilder */
        $vodBuilder = (new VodBuilder())->prepare();

        $builtVod = $vodBuilder->setLinkId($link->id)
            ->setMatchId($request->matchId)
            ->setAbout($request->name);

        $vod = $this->vodRepository->create($builtVod);

        return response()->json($vod, Response::HTTP_OK);
    }

    public function approveVod(ApproveVodRequest $request)
    {
        /** @var User $user */
        $user = auth()->user();

        /** @var Vod $vod */
        $vod = Vod::where("id", $request->vodId)->first();

        $admin = false;
        if ($user->id == 15) {
            $admin = true;
        }

        if ($user->cannot(EventModeratorRole::MODERATE_VODS, [$vod->match->group->event]) && !$admin) {
            return response()->json([
                "errors" => [
                    "message" => ["Not Authorized"]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        $request->validated();

        $this->vodRepository->approveVod($request->input("vodId"));
    }

    public function removeVod($vodId)
    {
        /** @var User $user */
        $user = auth()->user();

        /** @var Vod $vod */
        $vod = Vod::where("id", $vodId)->first();

        $admin = false;
        if ($user->id == 15) {
            $admin = true;
        }

        if ($user->cannot(EventModeratorRole::MODERATE_VODS, [$vod->match->group->event]) && !$admin) {
            return response()->json([
                "errors" => [
                    "message" => ["Not Authorized"]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $deleted = $this->vodRepository->delete($vodId);
        } catch (\Exception $exception) {
            return response()->json([
                "message" => "Something went wrong. Could not remove Vod."
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message" => "Successfully removed Vod."
        ], Response::HTTP_OK);
    }

    public function getMatchParticipants(Request $request)
    {
        $matches = MatchModel::where("id", $request->matchId)
            ->with("participants")
            ->with('participants.user')
            ->with('participants.page')
            ->with('participants.user.maniaplanet')
            ->get()
            ->map(function($match){
                return $match->participants;
            });

        if (!empty($matches->toArray())) {
            return $matches[0];
        }

        return [];
    }

    public function getPlayServerData($matchId)
    {
        if (!auth()->user()) {
            return response()->json([
                "errors" => [
                    "message" => ["Not Authorized"]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        /** @var MatchModel $match */
        $match = MatchModel::where("id", $matchId)->first();

        $hasAccess = $match->participants->where("user_id", auth()->user()->id)->count();

        if (!$hasAccess) {
            return response()->json([
                "errors" => [
                    "message" => ["Not Authorized"]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        /** @var DedicatedController $gameServer */
        $gameServer = DedicatedController::where("match_id", $matchId)->first();

        if (!$gameServer) {
            return response()->json([
                "message" => "No server allocated."
            ]);
        }

        /** @var DedicatedControllerProperty $roomName */
        $roomName = DedicatedControllerProperty::where("port", $gameServer->port)
            ->where("key", DedicatedControllerProperty::ROOM_NAME)
            ->first();

        if (!$roomName) {
            return response()->json([
                "errors" => [
                    "message" => ["Cannot get room name."]
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "clubName" => "esac.gg",
            "roomName" => $roomName->value
        ]);
    }
}
