<?php


namespace Modules\Match\Providers;


use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Modules\Match\Entities\LiveStream;
use Modules\Match\Entities\MatchModel;
use Modules\Match\Entities\MatchResult;
use Modules\Match\Entities\Vod;
use Modules\Match\Policies\LiveStreamPolicy;
use Modules\Match\Policies\MatchPolicy;
use Modules\Match\Policies\MatchResultPolicy;
use Modules\Match\Policies\VodPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
//        LiveStream::class => LiveStreamPolicy::class,
//        Match::class => MatchPolicy::class,
//        MatchResult::class => MatchResultPolicy::class,
//        Vod::class => VodPolicy::class
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
