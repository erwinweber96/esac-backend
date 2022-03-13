<?php

namespace Modules\Console\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;
use Modules\Console\Console\ClearGlobalAlerts;
use Modules\Console\Console\CreateRestartServerAlert;
use Modules\Console\Console\GiveMatchmakingCoins;
use Modules\Console\Console\PingDedicatedControllers;
use Modules\Console\Console\RestartServers;
use Modules\Console\Console\SchedulerCron1;
use Modules\Console\Console\SchedulerCron10;
use Modules\Console\Console\SchedulerCron10080;
use Modules\Console\Console\SchedulerCron1440;
use Modules\Console\Console\SchedulerCron15;
use Modules\Console\Console\SchedulerCron30;
use Modules\Console\Console\SchedulerCron5;
use Modules\Console\Console\SchedulerCron60;
use Modules\Console\Console\SchedulerCronTest;
use Modules\Console\Console\SetMatchmakingLive;
use Modules\Page\Console\CacheArticles;

class ConsoleServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = 'Console';

    /**
     * @var string $moduleNameLower
     */
    protected $moduleNameLower = 'console';

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->registerFactories();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
        $this->commands([
            PingDedicatedControllers::class,
            SchedulerCron1::class,
            SchedulerCron5::class,
            SchedulerCron10::class,
            SchedulerCron15::class,
            SchedulerCron30::class,
            SchedulerCron60::class,
            SchedulerCron1440::class,
            SchedulerCron10080::class,
            GiveMatchmakingCoins::class,
            SetMatchmakingLive::class,
            CacheArticles::class,
            ClearGlobalAlerts::class,
            CreateRestartServerAlert::class,
            RestartServers::class
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            module_path($this->moduleName, 'Config/config.php') => config_path($this->moduleNameLower . '.php'),
        ], 'config');
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'), $this->moduleNameLower
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/' . $this->moduleNameLower);

        $sourcePath = module_path($this->moduleName, 'Resources/views');

        $this->publishes([
            $sourcePath => $viewPath
        ], ['views', $this->moduleNameLower . '-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/' . $this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'Resources/lang'), $this->moduleNameLower);
        }
    }

    /**
     * Register an additional directory of factories.
     *
     * @return void
     */
    public function registerFactories()
    {
        if (! app()->environment('production') && $this->app->runningInConsole()) {
            app(Factory::class)->load(module_path($this->moduleName, 'Database/factories'));
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (\Config::get('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $this->moduleNameLower)) {
                $paths[] = $path . '/modules/' . $this->moduleNameLower;
            }
        }
        return $paths;
    }
}
