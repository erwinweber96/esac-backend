<?php


namespace Modules\User\Observers;


use App\Observers\ModelObserverInterface;
use Modules\User\Events\NewUserNotificationCreated;

class UserNotificationObserver implements ModelObserverInterface
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
//        event(new NewUserNotificationCreated($model));
    }

    public function updating($model)
    {
        // TODO: Implement updating() method.
    }

    public function updated($model)
    {
        // TODO: Implement updated() method.
    }

    public function saving($model)
    {
        // TODO: Implement saving() method.
    }

    public function saved($model)
    {
        event(new NewUserNotificationCreated($model));
    }

    public function deleting($model)
    {
        // TODO: Implement deleting() method.
    }

    public function deleted($model)
    {
        // TODO: Implement deleted() method.
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
