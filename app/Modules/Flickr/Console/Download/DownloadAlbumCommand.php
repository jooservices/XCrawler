<?php

namespace App\Modules\Flickr\Console\Download;

use App\Modules\Client\Repositories\IntegrationRepository;
use App\Modules\Core\Models\Task;
use App\Modules\Core\Services\States;
use App\Modules\Flickr\Events\PhotosetReadyForDownloadEvent;
use App\Modules\Flickr\Jobs\PhotosetPhotosJob;
use App\Modules\Flickr\Models\FlickrContact;
use App\Modules\Flickr\Models\FlickrPhotoset;
use App\Modules\Flickr\Services\FlickrService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Event;

class DownloadAlbumCommand extends Command
{
    public const COMMAND = 'flickr:download-album {--photoset_id=}';
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
    protected $description = 'Download all photos\'s album.';

    /**
     * @param IntegrationRepository $repository
     * @param FlickrService $flickrService
     * @return void
     * @throws \App\Modules\Core\Exceptions\HaveNoIntegration
     * @throws \App\Modules\Flickr\Exceptions\MissingEntityElement
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(
        IntegrationRepository $repository,
        FlickrService $flickrService
    ): void {
        $integration = $repository->getNonPrimary(FlickrService::SERVICE_NAME);

        $this->info('Integration: ' . $integration->name);

        $adapter = $flickrService->setIntegration($integration)->photosets;
        $photosetInfo = $adapter->getInfo((int) $this->option('photoset_id'));

        $this->info('Photoset info: ' . $photosetInfo->title . ' [' . $photosetInfo->id . ']');

        if (!$photosetInfo) {
            $this->warn('Can not get photoset ID: ' . $this->option('photoset_id'));
            return;
        }

        /**
         * Create contact if needed for relationship with photoset
         * @TODO Register task to fetch detail information
         */
        $contact = FlickrContact::updateOrCreate([
            'nsid' => $photosetInfo->owner
        ], []);

        $this->info('Contact: ' . $contact->nsid);

        /**
         * @var FlickrPhotoset $photoset
         */
        $photoset = FlickrPhotoset::updateOrCreate([
            'id' => $photosetInfo->id,
            'owner' => $contact->nsid,
        ], $photosetInfo->toArray());

        $this->info('Photoset: ' . $photoset->id);

        /**
         * This task should be done or completed after all photos are downloaded
         * @var Task $task
         */
        $task = $photoset->tasks()->create([
            'task' => FlickrService::TASK_DOWNLOAD_PHOTOSET,
            'state_code' => States::STATE_INIT,
            'payload' => [
                'photos' => $photosetInfo->photos
            ],
        ]);

        $this->info('Registered download task for photoset: ' . $photoset->id);
        $this->info('Task [' . $task->task . ']: ' . $task->uuid);

        // Check if all photoset's photos are fetch
        if ($photoset->relationshipPhotos()->count() !== $photoset->photos) {
            // Create task to fetch photoset's photos
            $subTask = $task->subTasks()->create([
                'model_type' => $task->model_type,
                'model_id' => $task->model_id,
                'task' => FlickrService::TASK_PHOTOSET_PHOTOS,
                'state_code' => States::STATE_INIT,
            ]);

            PhotosetPhotosJob::dispatch($integration, $subTask)->onQueue(FlickrService::QUEUE_NAME);
            $this->warn('There are no photos yet. Registered task to fetch photos of photoset');

            return;
        }

        Event::dispatch(new PhotosetReadyForDownloadEvent($task));
    }
}
