<?php


namespace Modules\User\Observers;


use Modules\User\Events\NewUserMessageSent;

class UserMessageObserver
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
//        event(new NewUserMessageSent($model));
    }

    public function updating($model)
    {
//        event(new MatchUpdated($model));
    }

    public function updated($model)
    {
//        event(new MatchUpdated($model));
    }

    public function saving($model)
    {
//        event(new MatchUpdated($model));
    }

    public function saved($model)
    {
        event(new NewUserMessageSent($model));
    }

    public function deleting($model)
    {
        // TODO: Implement deleting() method.
    }

    public function deleted($model)
    {
//        event(new MatchDeleted($model));
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
