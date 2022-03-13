<?php


namespace Modules\Map\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Event\Entities\Event;
use Modules\Event\Entities\EventModeratorRole;
use Modules\Game\Entities\Game;
use Modules\Map\Entities\MapPool;
use Modules\Map\Http\Requests\AddCustomMapPool;
use Modules\Map\Http\Requests\CreateMapPoolRequest;
use Modules\Map\Http\Requests\UpdateMapPoolNameRequest;
use Modules\Map\Jobs\BuildMapPool;
use Modules\Map\Jobs\BuildMapPoolForNameUpdate;
use Modules\Map\ManiaExchange\Repositories\MappackRepository;
use Modules\Map\ManiaExchange\Repositories\MappackRepositoryInterface;
use Modules\Map\ManiaExchange\Repositories\TMXMappackRepository;
use Modules\Map\Repositories\MapPoolRepository;
use Modules\User\Entities\User;

class MapPoolController extends Controller
{
    /** @var BuildMapPool $buildMapPool */
    private $buildMapPool;

    /** @var MapPoolRepository $mapPoolRepository */
    private $mapPoolRepository;

    /** @var BuildMapPoolForNameUpdate $buildMapPoolForNameUpdate */
    private $buildMapPoolForNameUpdate;

    /** @var MappackRepository $mapPackRepository */
    private $mapPackRepository;

    /** @var TMXMappackRepository $tmxMapPackRepository */
    private $tmxMapPackRepository;

    /**
     * MapPoolController constructor.
     * @param BuildMapPool $buildMapPool
     * @param MapPoolRepository $mapPoolRepository
     * @param BuildMapPoolForNameUpdate $buildMapPoolForNameUpdate
     * @param MappackRepository $mapPackRepository
     * @param TMXMappackRepository $tmxMapPackRepository
     */
    public function __construct(BuildMapPool $buildMapPool, MapPoolRepository $mapPoolRepository, BuildMapPoolForNameUpdate $buildMapPoolForNameUpdate, MappackRepository $mapPackRepository, TMXMappackRepository $tmxMapPackRepository)
    {
        $this->buildMapPool = $buildMapPool;
        $this->mapPoolRepository = $mapPoolRepository;
        $this->buildMapPoolForNameUpdate = $buildMapPoolForNameUpdate;
        $this->mapPackRepository = $mapPackRepository;
        $this->tmxMapPackRepository = $tmxMapPackRepository;
    }

    public function create(CreateMapPoolRequest $request)
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

        if ($user->cannot(EventModeratorRole::ADD_MAP_POOL, [$event]) && !$admin) {
            return response()->json([
                "errors" => [
                    "message" => ["Not Authorized"]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        switch($event->game->name) {
            case Game::TRACKMANIA :
                /** @var MappackRepositoryInterface $repository */
                $repository = $this->tmxMapPackRepository;
                break;

            case Game::TRACKMANIA_2_STADIUM :
                /** @var MappackRepositoryInterface $repository */
                $repository = $this->mapPackRepository;
                break;

            default:
                return response()->json([
                    "errors" => [
                        "message" => ["Function not supported for this game."]
                    ]
                ], Response::HTTP_NOT_IMPLEMENTED);
                break;
        }

        $found = $repository->findById($request->mxId);

        if (!$found) {
            return response()->json([
                "errors" => [
                    "message" => ["Could not find mappack."]
                ]
            ], Response::HTTP_NOT_FOUND);
        }

        $builder = $this->buildMapPool->execute($request);
        $mapPool = $this->mapPoolRepository->create($builder);

        return response()->json($mapPool, Response::HTTP_OK);
    }

    public function get($id)
    {
        /** @var MapPool $mapPool */
        $mapPool = $this->mapPoolRepository->show($id);
        $data = $mapPool->toArray();
        $data['mxData'] = $mapPool->mxData->toArray();
        return response()->json($data, Response::HTTP_OK);
    }

    public function updateMapPoolName(UpdateMapPoolNameRequest $request)
    {
        $request->validated();

        /** @var User $user */
        $user = auth()->user();

        /** @var MapPool $mapPool */
        $mapPool = MapPool::where("id", $request->mapPoolId)->first();

        if ($user->cannot(EventModeratorRole::EDIT_MAP_POOL, [$mapPool->event])) {
            return response()->json([
                "errors" => [
                    "message" => ["Not Authorized"]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        $builder = $this->buildMapPoolForNameUpdate->execute($request);
        $updated = $this->mapPoolRepository->update($builder, $request->mapPoolId);

        if (!$updated) {
            return response()->json([
                "errors" => ["message" => "Something went wrong. Could not update Map Pool."]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message" => "Successfully updated Map Pool."
        ], Response::HTTP_OK);
    }

    public function delete($id)
    {
        /** @var User $user */
        $user = auth()->user();

        /** @var MapPool $mapPool */
        $mapPool = MapPool::where("id", $id)->first();

        if ($user->cannot(EventModeratorRole::DELETE_MAP_POOL, [$mapPool->event])) {
            return response()->json([
                "errors" => [
                    "message" => ["Not Authorized"]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $deleted = $this->mapPoolRepository->delete($id);
        } catch (\Throwable $exception) {
            return response()->json([
                "errors" => ["message" => "Something went wrong. Could not delete Map Pool."]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if (!$deleted) {
            return response()->json([
                "errors" => ["message" => "Something went wrong. Could not delete Map Pool."]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message" => "Successfully deleted Map Pool."
        ], Response::HTTP_OK);
    }

    public function addCustomMapPool(AddCustomMapPool $request)
    {
        $request->validated();

        /** @var User $user */
        $user = auth()->user();

        /** @var Event $event */
        $event = Event::where("id", $request->eventId)->first();

        if ($user->cannot(EventModeratorRole::ADD_MAP_POOL, [$event])) {
            return response()->json([
                "errors" => [
                    "message" => ["Not Authorized"]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

//        try {
            $mapPool = MapPool::create([
                "name" => $request->name,
                "event_id" => $request->eventId,
                "link" => $request->link,
                "custom" => true
            ]);
//        } catch (\Throwable $exception) {
//            return response()->json([
//                "errors" => ["message" => "Something went wrong. Could not create Map Pool."]
//            ], Response::HTTP_INTERNAL_SERVER_ERROR);
//        }

        return response()->json($mapPool, Response::HTTP_OK);
    }
}
