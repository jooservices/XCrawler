<?php

namespace App\Modules\Flickr\Providers;

use App\Modules\Flickr\Console\Album\DownloadPhotosCommand;
use App\Modules\Flickr\Console\Contact\FavoritesCommand;
use App\Modules\Flickr\Console\Contact\InfoCommand;
use App\Modules\Flickr\Console\Contact\PhotosCommand;
use App\Modules\Flickr\Console\Contact\PhotosetsCommand;
use App\Modules\Flickr\Console\ContactsCommand;
use App\Modules\Flickr\Console\Download\DownloadAlbumCommand;
use App\Modules\Flickr\Console\Download\PhotoUploadCommand;
use App\Modules\Flickr\Console\MigrateContacts;
use App\Modules\Flickr\Console\Photoset\PhotosCommand as PhotosetPhotosCommand;
use App\Modules\Flickr\Console\PhotosSizesCommand;
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
            ContactsCommand::class,
            FavoritesCommand::class,
            PhotosCommand::class,
            SyncContactTasksCommand::class,
            PhotosSizesCommand::class,
            PhotosetsCommand::class,
            PhotosetPhotosCommand::class,

            DownloadAlbumCommand::class,
            PhotoUploadCommand::class,

            InfoCommand::class
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
