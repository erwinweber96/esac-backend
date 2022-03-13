<?php


namespace Modules\Event\Observers;


use App\Observers\ModelObserverInterface;
use Modules\Event\Events\EventCreated;
use Modules\Event\Events\EventDeleted;
use Modules\Event\Events\EventUpdated;

/**
 * Class EventObserver
 * @package Modules\Events\Observers
 */
class EventObserver implements ModelObserverInterface
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
        event(new EventCreated($model));
    }

    public function updating($model)
    {
        // TODO: Implement updating() method.
    }

    public function updated($model)
    {
        event(new EventUpdated($model));
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
        event(new EventDeleted($model));
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
