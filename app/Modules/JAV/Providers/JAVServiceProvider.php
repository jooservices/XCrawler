<?php

namespace App\Modules\JAV\Providers;

use App\Modules\JAV\Console\Onejav\AllCommand;
use App\Modules\JAV\Console\Onejav\DailyCommand;
use App\Modules\JAV\Console\Onejav\ItemsCommand;
use App\Modules\JAV\Console\Onejav\Migrate;
use App\Modules\JAV\Console\Onejav\TagsCommand;
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

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
        $this->commands([
            ItemsCommand::class,
            DailyCommand::class,
            AllCommand::class,
            TagsCommand::class,
            Migrate::class
        ]);
    }
}
