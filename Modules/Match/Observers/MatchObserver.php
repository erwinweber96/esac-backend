<?php


namespace Modules\Match\Observers;


use App\Observers\ModelObserverInterface;
use Modules\Match\Events\MatchCreated;
use Modules\Match\Events\MatchDeleted;
use Modules\Match\Events\MatchUpdated;

/**
 * Class MatchObserver
 * @package Modules\Match\Observers
 */
class MatchObserver implements ModelObserverInterface
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
        event(new MatchCreated($model));
    }

    public function updating($model)
    {
//        event(new MatchUpdated($model));
    }

    public function updated($model)
    {
        event(new MatchUpdated($model));
    }

    public function saving($model)
    {
//        event(new MatchUpdated($model));
    }

    public function saved($model)
    {
        event(new MatchUpdated($model));
    }

    public function deleting($model)
    {
        // TODO: Implement deleting() method.
    }

    public function deleted($model)
    {
        event(new MatchDeleted($model));
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
