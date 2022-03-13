<?php


namespace Modules\Match\Observers;


use App\Observers\ModelObserverInterface;
use Modules\Match\Events\MatchResultCreated;
use Modules\Match\Events\MatchResultDeleted;
use Modules\Match\Events\MatchResultUpdated;

class MatchResultObserver implements ModelObserverInterface
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
        event(new MatchResultCreated($model));
    }

    public function updating($model)
    {
        // TODO: Implement updating() method.
    }

    public function updated($model)
    {
        event(new MatchResultUpdated($model));
    }

    public function saving($model)
    {
        // TODO: Implement saving() method.
    }

    public function saved($model)
    {
        event(new MatchResultCreated($model));
    }

    public function deleting($model)
    {
        // TODO: Implement deleting() method.
    }

    public function deleted($model)
    {
        event(new MatchResultDeleted($model));
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
