<?php

namespace App\Modules\JAV\Providers;

use App\Modules\JAV\Console\Onejav\CrawlingAll;
use App\Modules\JAV\Console\Onejav\CrawlingDaily;
use App\Modules\JAV\Console\Onejav\CrawlingItems;
use Illuminate\Support\ServiceProvider;

class JAVServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = 'JAV';

    /**
     * @var string $moduleNameLower
     */
    protected $moduleNameLower = 'jav';

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerConfig();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
        $this->commands([
            CrawlingItems::class,
            CrawlingDaily::class,
            CrawlingAll::class,
        ]);
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
            module_path($this->moduleName, 'Config/config.php'),
            $this->moduleNameLower
        );
    }
}
