<?php

namespace App\Modules\Flickr\Listeners;

use App\Modules\Core\StateMachine\Task\CompletedState;
use App\Modules\Core\StateMachine\Task\DownloadedState;
use App\Modules\Core\StateMachine\Task\InProgressState;
use App\Modules\Flickr\Events\FetchPhotosetPhotosCompletedEvent;
use App\Modules\Flickr\Events\PhotosetCreatedEvent;
use App\Modules\Flickr\Events\PhotosetPhotoDownloadCompletedEvent;
use App\Modules\Flickr\Events\PhotosetPhotoReadyForUploadEvent;
use App\Modules\Flickr\Events\PhotosetReadyForDownloadEvent;
use App\Modules\Flickr\Jobs\DownloadPhotoJob;
use App\Modules\Flickr\Services\FlickrService;
use App\Modules\Flickr\Services\TaskService;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Event;

class PhotosetEventSubscriber
{
    public function onPhotosetCreated(PhotosetCreatedEvent $event): void
    {
        $event->photoset->tasks()->create([
            'task' => TaskService::TASK_PHOTOSET_PHOTOS,
        ]);
    }

    public function onFetchPhotosetPhotosCompleted(FetchPhotosetPhotosCompletedEvent $event)
    {
        $event->task->transitionTo(CompletedState::class);

        if (
            $event->task->parentTask()->exists()
            && $event->task->parentTask->task === TaskService::TASK_DOWNLOAD_PHOTOSET
        ) {
            Event::dispatch(new PhotosetReadyForDownloadEvent($event->task->parentTask));
        }
    }

    /**
     * @param PhotosetReadyForDownloadEvent $event
     * @return void
     */
    public function onPhotosetReadyForDownload(PhotosetReadyForDownloadEvent $event)
    {
        $event->task->transitionTo(InProgressState::class);
        $event->task->model->relationshipPhotos->each(function ($photo) use ($event) {
            $task = $photo->tasks()->create([
                'task' => TaskService::TASK_DOWNLOAD_PHOTOSET_PHOTO,
                'task_id' => $event->task->id, // 'parent_task_id
            ]);

            DownloadPhotoJob::dispatch($task)->onQueue(FlickrService::QUEUE_NAME);
        });
    }

    public function onPhotosetPhotoDownloadCompletedEvent(PhotosetPhotoDownloadCompletedEvent $event)
    {
        $event->task->transitionTo(DownloadedState::class);

        $parentTask = $event->task->parentTask;
        $photoset = $parentTask->model;
        $totalPhotos = $parentTask->payload['photos'];

        $downloadedPhotos = $parentTask
            ->subTasks()
            ->where('task', TaskService::TASK_DOWNLOAD_PHOTOSET_PHOTO)
            ->whereState('state_code', DownloadedState::class)
            ->count();

        if ($totalPhotos !== $downloadedPhotos) {
            return;
        }

        $parentTask->transitionTo(DownloadedState::class);
        Event::dispatch(new PhotosetPhotoReadyForUploadEvent($photoset));
    }

    public function onPhotosetPhotoReadyForUploadEvent(PhotosetPhotoReadyForUploadEvent $event)
    {
        $photoset = $event->photoset;
        /**
         * Create Google Photos album
         */
        $photoset->createGooglePhotoAlbum();
        $photos = $photoset->relationshipPhotos;
        $task = $photoset->tasks()->create([
            'task' => TaskService::TASK_UPLOAD_PHOTOSET,
            'payload' => [
                'photos' => $photos->count(),
            ]
        ]);
        $task->transitionTo(InProgressState::class);

        foreach ($photos as $photo) {
            $task->subTasks()->create([
               'model_type' => $photo->getMorphClass(),
               'model_id' => $photo->id,
               'task' => TaskService::TASK_UPLOAD_PHOTO,
            ]);
        }
    }

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(
            PhotosetCreatedEvent::class,
            [self::class, 'onPhotosetCreated']
        );

        $events->listen(
            FetchPhotosetPhotosCompletedEvent::class,
            [self::class, 'onFetchPhotosetPhotosCompleted']
        );

        $events->listen(
            PhotosetReadyForDownloadEvent::class,
            [self::class, 'onPhotosetReadyForDownload']
        );

        $events->listen(
            PhotosetPhotoDownloadCompletedEvent::class,
            [self::class, 'onPhotosetPhotoDownloadCompletedEvent']
        );

        $events->listen(
            PhotosetPhotoReadyForUploadEvent::class,
            [self::class, 'onPhotosetPhotoReadyForUploadEvent']
        );
    }
}
