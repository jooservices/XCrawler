<?php

namespace App\Modules\Flickr\Console;

use App\Modules\Client\Repositories\IntegrationRepository;
use App\Modules\Core\Facades\Setting;
use App\Modules\Flickr\Jobs\PhotosizesJob;
use App\Modules\Flickr\Repositories\PhotoRepository;
use App\Modules\Flickr\Services\FlickrService;
use Illuminate\Console\Command;

class PhotosSizesCommand extends Command
{
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

        /**
         * Only process on non-primary integrations
         */
        app(IntegrationRepository::class)->getNonPrimaryItems(FlickrService::SERVICE_NAME)
            ->each(function ($integration) use ($photoRepository) {
                $this->output->text('Processing integration: ' . $integration->name);

                $photoRepository->getNoSizesPhotos(Setting::remember('flickr', 'task_photos_sizes_limit', fn() => 10))
                    ->each(function ($photo) use ($integration) {
                        $this->info('Processing photo ' . $photo->id . ' with integration ' . $integration->name);
                        PhotosizesJob::dispatch($integration, $photo)->onQueue(FlickrService::QUEUE_NAME);
                    });
            });
    }
}
