<?php


namespace Modules\Event\Providers;


use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Modules\Event\Entities\Event;
use Modules\Event\Entities\EventFaq;
use Modules\Event\Entities\Participant;
use Modules\Event\Policies\EventFaqPolicy;
use Modules\Event\Policies\EventPolicy;
use Modules\Event\Policies\ParticipantPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Event::class => EventPolicy::class
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
    }
}
