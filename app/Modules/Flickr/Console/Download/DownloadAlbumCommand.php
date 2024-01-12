<?php

namespace App\Modules\Flickr\Console\Download;

use App\Modules\Client\Models\Integration;
use App\Modules\Client\Repositories\IntegrationRepository;
use App\Modules\Client\Services\GooglePhotos;
use App\Modules\Core\Exceptions\NoIntegrateException;
use App\Modules\Core\Models\Task;
use App\Modules\Core\Services\States;
use App\Modules\Flickr\Events\PhotosetReadyForDownloadEvent;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\MissingEntityElement;
use App\Modules\Flickr\Jobs\PeopleInfoJob;
use App\Modules\Flickr\Jobs\PhotosetPhotosJob;
use App\Modules\Flickr\Models\FlickrPhotoset;
use App\Modules\Flickr\Models\GooglePhotoAlbum;
use App\Modules\Flickr\Services\Flickr\Entities\PhotosetEntity;
use App\Modules\Flickr\Services\FlickrContactService;
use App\Modules\Flickr\Services\FlickrService;
use Google\ApiCore\ApiException;
use Google\ApiCore\ValidationException;
use GuzzleHttp\Exception\GuzzleException;
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
     * @return void
     * @throws NoIntegrateException
     */
    public function handle(
        IntegrationRepository $repository
    ): void {
        $this->info('Getting integration');

        $integration = $repository->getNonPrimary(FlickrService::SERVICE_NAME);

        $this->line('Integration: <info>' . $integration->id . '</info>');

        if (!$photosetInfo = $this->fetchPhotosetInfo($integration)) {
            return;
        }

        $this->info('Preparing ...');

        /**
         * Create contact if needed for relationship with photoset
         */
        $contact = app(FlickrContactService::class)->create(['nsid' => $photosetInfo->owner]);
        $this->line('Contact: <info>' . $contact->nsid . '</info>');
        PeopleInfoJob::dispatch($contact->nsid)->onQueue(FlickrService::QUEUE_NAME);
        $this->line('Dispatched people info job');

        /**
         * @var FlickrPhotoset $photoset
         */
        $photoset = FlickrPhotoset::updateOrCreate([
            'id' => $photosetInfo->id,
            'owner' => $contact->nsid,
        ], $photosetInfo->toArray());

        $this->line('Photoset: <info>' . $photoset->id . '</info>');

        $googlePhotoAlbum = $photoset->googlePhotoAlbum;
        if (!$googlePhotoAlbum) {
            $this->warn('Google Album not found. Creating ...');
            $googlePhotoAlbum = $this->createGooglePhotoAlbum($photoset);
        }

        $this->line('Google Album: <info>' . $googlePhotoAlbum->album_id . '</info>');

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

        $this->line('Registered download task for photoset: <info>' . $task->uuid . '</info>');

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

        $this->info('All photos are ready. Dispatching event to download photoset');

        Event::dispatch(new PhotosetReadyForDownloadEvent($task));
    }

    /**
     * @throws GuzzleException
     * @throws MissingEntityElement
     */
    private function fetchPhotosetInfo(Integration $integration): ?PhotosetEntity
    {
        $adapter = app(FlickrService::class)->setIntegration($integration)->photosets;
        $photosetEntity = $adapter->getInfo((int)$this->option('photoset_id'));

        if (!$photosetEntity) {
            $this->warn('Can not get photoset ID: ' . $this->option('photoset_id'));
            return null;
        }

        $this->line('Photoset info: <info>' . $photosetEntity->title . ' [' . $photosetEntity->id . ']</info>');

        return $photosetEntity;
    }

    /**
     * @throws ValidationException
     * @throws ApiException
     */
    private function createGooglePhotoAlbum(FlickrPhotoset $photoset): GooglePhotoAlbum
    {
        $googlePhotoService = app(GooglePhotos::class);
        $googleAlbumId = $googlePhotoService->createAlbum($photoset->title);

        $this->line('Created Google Album: <info>' . $googleAlbumId . '</info>');

        return $photoset->googlePhotoAlbum()->create([
            'album_id' => $googleAlbumId,
            'title' => $photoset->title,
        ]);
    }
}
