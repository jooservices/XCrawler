<?php

namespace App\Modules\Flickr\Providers;

use App\Modules\Flickr\Console\Contact\FavoritesCommand;
use App\Modules\Flickr\Console\Contact\PhotosCommand;
use App\Modules\Flickr\Console\ContactCommand;
use App\Modules\Flickr\Console\SyncContactTasksCommand;
use Illuminate\Support\ServiceProvider;

class FlickrServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected string $moduleName = 'Flickr';

    /**
     * @var string $moduleNameLower
     */
    protected string $moduleNameLower = 'flickr';

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->registerConfig();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig(): void
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
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
        $this->commands([
            ContactCommand::class,
            FavoritesCommand::class,
            PhotosCommand::class,
            SyncContactTasksCommand::class
        ]);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides(): array
    {
        return [];
    }
}
