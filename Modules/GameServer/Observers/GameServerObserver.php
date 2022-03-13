<?php


namespace Modules\GameServer\Observers;


use App\Observers\ModelObserverInterface;
use Modules\GameServer\Events\GameServerAdded;
use Modules\GameServer\Events\GameServerDeleted;
use Modules\GameServer\Events\GameServerUpdated;

class GameServerObserver implements ModelObserverInterface
{
    public function retrieved($model)
    {
        // TODO: Implement retrieved() method.
    }

    public function creating($model)
    {
        // TODO: Implement creating() method.
    }

    public function created($model)
    {
//        event(new GameServerAdded($model));
    }

    public function updating($model)
    {
        // TODO: Implement updating() method.
    }

    public function updated($model)
    {
        event(new GameServerUpdated($model));
    }

    public function saving($model)
    {
        // TODO: Implement saving() method.
    }

    public function saved($model)
    {
        // TODO: Implement saved() method.
    }

    public function deleting($model)
    {
        // TODO: Implement deleting() method.
    }

    public function deleted($model)
    {
        event(new GameServerDeleted($model));
    }

    public function restoring($model)
    {
        // TODO: Implement restoring() method.
    }

    public function restored($model)
    {
        // TODO: Implement restored() method.
    }

}
