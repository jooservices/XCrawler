<?php

namespace App\Modules\Flickr\Console\Download;

use App\Modules\Client\Models\Integration;
use App\Modules\Client\Repositories\IntegrationRepository;
use App\Modules\Core\Models\Task;
use App\Modules\Core\StateMachine\Task\InProgressState;
use App\Modules\Flickr\Events\PhotosetReadyForDownloadEvent;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\MissingEntityElement;
use App\Modules\Flickr\Jobs\PeopleInfoJob;
use App\Modules\Flickr\Jobs\PhotosetPhotosJob;
use App\Modules\Flickr\Models\FlickrContact;
use App\Modules\Flickr\Models\FlickrPhotoset;
use App\Modules\Flickr\Services\Flickr\Entities\PhotosetEntity;
use App\Modules\Flickr\Services\FlickrContactService;
use App\Modules\Flickr\Services\FlickrService;
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

    private Integration $integration;

    /**
     * @return void
     * @throws GuzzleException
     * @throws MissingEntityElement
     */
    public function handle(): void
    {
        $this->info('Preparing ...');

        $this->integration();

        if (!$photosetInfo = $this->getPhotosetInfo()) {
            return;
        }

        $contact = $this->prepareContact($photosetInfo->owner);
        $photoset = $this->preparePhotoset($photosetInfo, $contact);

        /**
         * We don't need to create Google Photo Album at this time
         */

        $task = $this->prepareTask($photoset, $photosetInfo);

        // Check if all photoset's photos are fetch
        if ($photoset->relationshipPhotos()->count() !== $photoset->photos) {
            $this->preparePhotos($task);
            return;
        }

        $this->info('All photos are ready. Dispatching event to download photoset');

        Event::dispatch(new PhotosetReadyForDownloadEvent($task));
    }

    private function integration(): Integration
    {
        $this->info('Getting integration');
        $this->integration = app(IntegrationRepository::class)->getNonPrimary(FlickrService::SERVICE_NAME);
        $this->line('Integration: <info>' . $this->integration->id . '</info>');

        return $this->integration;
    }

    /**
     * @throws GuzzleException
     * @throws MissingEntityElement
     */
    private function getPhotosetInfo(): ?PhotosetEntity
    {
        $this->info('Getting photoset info');
        $adapter = app(FlickrService::class)->setIntegration($this->integration)->photosets;
        $photosetEntity = $adapter->getInfo((int)$this->option('photoset_id'));

        if (!$photosetEntity) {
            $this->warn('Can not get photoset ID: ' . $this->option('photoset_id'));
            return null;
        }

        $this->line('Photoset info: <info>' . $photosetEntity->title . ' [' . $photosetEntity->id . ']</info>');

        return $photosetEntity;
    }

    private function prepareContact(string $owner): FlickrContact
    {
        $this->info('Getting contact');
        /**
         * Create contact if needed for relationship with photoset
         */
        $contact = app(FlickrContactService::class)->create(['nsid' => $owner]);
        $this->line('Contact: <info>' . $contact->nsid . '</info>');
        PeopleInfoJob::dispatch($contact->nsid)->onQueue(FlickrService::QUEUE_NAME);
        $this->warn('Dispatched people info job');

        return $contact;
    }

    private function preparePhotoset(PhotosetEntity $photosetInfo, FlickrContact $contact): FlickrPhotoset
    {
        $this->info('Getting photoset');
        /**
         * @var FlickrPhotoset $photoset
         */
        $photoset = FlickrPhotoset::updateOrCreate([
            'id' => $photosetInfo->id,
            'owner' => $contact->nsid,
        ], $photosetInfo->toArray());

        $this->line('Photoset: <info>' . $photoset->id . '</info>');

        return $photoset;
    }

    private function prepareTask(FlickrPhotoset $photoset, PhotosetEntity $photosetInfo): Task
    {
        $this->info('Preparing task');
        /**
         * This task should be done or completed after all photos are downloaded
         * @var Task $task
         */
        $task = $photoset->tasks()->create([
            'task' => FlickrService::TASK_DOWNLOAD_PHOTOSET,
            'payload' => [
                'photos' => $photosetInfo->photos
            ],
        ]);

        $this->line('Registered ' . FlickrService::TASK_DOWNLOAD_PHOTOSET . ' for photoset: <info>' . $task->uuid . '</info>');

        return $task;
    }

    private function preparePhotos(Task $task): void
    {
        $this->info('Preparing photos');
        // Create task to fetch photoset's photos
        $subTask = $task->subTasks()->create([
            'model_type' => $task->model_type,
            'model_id' => $task->model_id,
            'task' => FlickrService::TASK_PHOTOSET_PHOTOS,
        ]);
        $subTask->state_code->transitionTo(InProgressState::class);

        PhotosetPhotosJob::dispatch($this->integration, $subTask)->onQueue(FlickrService::QUEUE_NAME);
        $this->warn('There are no photos yet. Registered task to fetch photos of photoset');
    }
}
