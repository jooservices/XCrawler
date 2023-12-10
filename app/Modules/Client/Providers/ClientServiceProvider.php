<?php

namespace App\Modules\Client\Providers;

use App\Modules\Client\Console\IntegrationCommand;
use App\Modules\Client\Console\Integration\AddCommand;
use App\Modules\Client\OAuth\Storage\Memory;
use App\Modules\Client\OAuth\Storage\TokenStorageInterface;
use Exception;
use Illuminate\Support\ServiceProvider;

class ClientServiceProvider extends ServiceProvider
{
    /**
     * @var string
     */
    protected $moduleName = 'Client';

    /**
     * @var string
     */
    protected $moduleNameLower = 'client';

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
        $this->app->bind(TokenStorageInterface::class, function () {
            switch (config('core.oauth.storage', 'memory')) {
                case 'memory':
                    return new Memory();
                default:
                    throw new Exception('Invalid storage type');
            }
        });

        $this->commands([
            IntegrationCommand::class,
            AddCommand::class
        ]);
    }
}
