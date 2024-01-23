<?php

namespace App\Modules\Flickr\Console\Download;

use App\Modules\Client\Models\Integration;
use App\Modules\Core\Console\Traits\HasIntegrationsCommand;
use App\Modules\Core\Models\Task;
use App\Modules\Core\StateMachine\Task\InProgressState;
use App\Modules\Flickr\Events\PhotosetReadyForDownloadEvent;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\FailedException;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\InvalidRespondException;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\MissingEntityElement;
use App\Modules\Flickr\Jobs\PeopleInfoJob;
use App\Modules\Flickr\Jobs\PhotosetPhotosJob;
use App\Modules\Flickr\Models\FlickrContact;
use App\Modules\Flickr\Models\FlickrPhotoset;
use App\Modules\Flickr\Services\Flickr\Entities\PhotosetEntity;
use App\Modules\Flickr\Services\FlickrContactService;
use App\Modules\Flickr\Services\FlickrService;
use App\Modules\Flickr\Services\TaskService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Event;

class DownloadAlbumCommand extends Command
{
    use HasIntegrationsCommand;

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
    protected $description = 'Download all album\'s photos.';

    /**
     * @return void
     * @throws GuzzleException
     * @throws MissingEntityElement
     */
    public function handle(): void
    {
        $this->info('Download photoset: <fg=blue>' . $this->option('photoset_id') . '</> ...');

        $this->processNonePrimaryIntegration(FlickrService::SERVICE_NAME, function (Integration $integration) {
            if (!$photosetInfo = $this->getPhotosetInfo($integration)) {
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
                $this->preparePhotos($task, $integration);
                return;
            }

            $this->info('All photos are ready. Dispatching event to download photoset');

            Event::dispatch(new PhotosetReadyForDownloadEvent($task));
        });
    }

    private function prepareContact(string $owner): FlickrContact
    {
        $this->info('Getting contact: <fg=blue>' . $owner . '</>');
        /**
         * Create contact if needed for relationship with photoset
         */
        $contact = app(FlickrContactService::class)->create(['nsid' => $owner]);
        $this->line('Contact: <info>' . $contact->nsid . '</info>');

        PeopleInfoJob::dispatch($contact->nsid)->onQueue(FlickrService::QUEUE_NAME);
        $this->warn('Dispatched people info job');

        return $contact;
    }

    /**
     * @param Integration $integration
     * @return PhotosetEntity|null
     * @throws GuzzleException
     * @throws FailedException
     * @throws InvalidRespondException
     */
    private function getPhotosetInfo(Integration $integration): ?PhotosetEntity
    {
        $this->info('Getting photoset info');
        $adapter = app(FlickrService::class)->setIntegration($integration)->photosets;
        $photosetEntity = $adapter->getInfo((int)$this->option('photoset_id'));

        if (!$photosetEntity) {
            $this->warn('Can not get photoset ID: ' . $this->option('photoset_id'));
            return null;
        }

        $this->line('Photoset info: <info>' . $photosetEntity->title . ' [' . $photosetEntity->id . ']</info>');

        return $photosetEntity;
    }

    private function preparePhotoset(PhotosetEntity $photosetInfo, FlickrContact $contact): FlickrPhotoset
    {
        $this->info('Getting photoset: <fg=blue>' . $photosetInfo->id . '</>');
        /**
         * @var FlickrPhotoset $photoset
         */
        $photoset = FlickrPhotoset::updateOrCreate([
            'id' => $photosetInfo->id,
            'owner' => $contact->nsid,
        ], $photosetInfo->toArray());

        $this->line('Photoset has <fg=blue>' . $photosetInfo->photos . '</> photos');

        return $photoset;
    }

    private function prepareTask(FlickrPhotoset $photoset, PhotosetEntity $photosetInfo): ?Task
    {
        $this->info('Preparing task');
        /**
         * This task will be transitioned to Downloaded when all photos are downloaded
         * - And will be deleted as soon as all photos are downloaded
         * @var Task $task
         */
        $task = $photoset->tasks()->where('task', TaskService::TASK_DOWNLOAD_PHOTOSET)->first();
        if ($task) {
            $this->warn('Task already exists');
        } else {
            $task = $photoset->tasks()->create([
                'task' => TaskService::TASK_DOWNLOAD_PHOTOSET,
                'payload' => [
                    'photos' => $photosetInfo->photos
                ],
            ]);
        }

        $this->line('Registered task <options=bold;fg=blue>' . TaskService::TASK_DOWNLOAD_PHOTOSET . '</> for photoset: <fg=blue>' . $task->uuid . '</>');

        return $task;
    }

    private function preparePhotos(Task $task, Integration $integration): void
    {
        $this->info('Preparing photos');
        $task->transitionTo(InProgressState::class);

        // Create task to fetch photoset's photos
        $subTask = $task->subTasks()->where('task', TaskService::TASK_PHOTOSET_PHOTOS)->first();
        if ($subTask) {
            $this->warn('Task already exists');
        } else {
            $subTask = $task->subTasks()->create([
                'model_type' => $task->model_type,
                'model_id' => $task->model_id,
                'task' => TaskService::TASK_PHOTOSET_PHOTOS,
            ]);
            $this->line('Registered task <options=bold;fg=blue>' . $subTask->task . '</>');
        }

        PhotosetPhotosJob::dispatch($integration, $subTask)->onQueue(FlickrService::QUEUE_NAME);
        $this->warn('There are no photos yet. Registered task to fetch photos of photoset');
    }
}
