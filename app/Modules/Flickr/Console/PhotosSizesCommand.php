<?php

namespace App\Modules\Flickr\Console;

use App\Modules\Core\Console\Traits\HasIntegrationsCommand;
use App\Modules\Core\Facades\Setting;
use App\Modules\Flickr\Jobs\PhotosizesJob;
use App\Modules\Flickr\Repositories\PhotoRepository;
use App\Modules\Flickr\Services\FlickrService;
use Illuminate\Console\Command;

class PhotosSizesCommand extends Command
{
    use HasIntegrationsCommand;

    public const COMMAND = 'flickr:photos-sizes';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch photos\' sizes from Flickr';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->info('Fetching photos\' sizes...');

        $photoRepository = app(PhotoRepository::class);

        $this->processNonePrimaryIntegrations(
            FlickrService::SERVICE_NAME,
            function ($integration) use ($photoRepository) {

                $photoRepository->getNoSizesPhotos(Setting::remember('flickr', 'task_photos_sizes_limit', fn() => 10))
                    ->each(function ($photo) use ($integration) {
                        $this->output->text('Processing photo <fg=blue>' . $photo->id . '</>');
                        PhotosizesJob::dispatch($integration, $photo)->onQueue(FlickrService::QUEUE_NAME);
                    });
            }
        );
    }
}
