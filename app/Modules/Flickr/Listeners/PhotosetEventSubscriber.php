<?php

namespace App\Modules\Flickr\Listeners;

use App\Modules\Core\Services\States;
use App\Modules\Flickr\Events\FetchPhotosetPhotosCompletedEvent;
use App\Modules\Flickr\Events\PhotosetCreatedEvent;
use App\Modules\Flickr\Events\PhotosetPhotoDownloadCompletedEvent;
use App\Modules\Flickr\Events\PhotosetReadyForDownload;
use App\Modules\Flickr\Jobs\DownloadPhotoJob;
use App\Modules\Flickr\Services\FlickrService;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Event;

class PhotosetEventSubscriber
{
    public function onPhotosetCreated(PhotosetCreatedEvent $event): void
    {
        $event->photoset->tasks()->create([
            'task' => FlickrService::TASK_PHOTOSET_PHOTOS,
            'state_code' => States::STATE_INIT,
        ]);
    }

    public function onFetchPhotosetPhotosCompleted(FetchPhotosetPhotosCompletedEvent $event)
    {
        $event->task->update([
            'state_code' => States::STATE_COMPLETED,
        ]);

        if ($event->task->parentTask && $event->task->parentTask->task === FlickrService::TASK_DOWNLOAD_PHOTOSET) {
            Event::dispatch(new PhotosetReadyForDownload($event->task->parentTask));
        }
    }

    public function onPhotosetReadyForDownload(PhotosetReadyForDownload $event)
    {
        $event->task->update([
            'state_code' => States::STATE_IN_PROGRESS
        ]);

        $photoset = $event->task->model;

        $photoset->relationshipPhotos->each(function ($photo) use ($event) {
            $task = $photo->tasks()->create([
                'task' => FlickrService::TASK_DOWNLOAD_PHOTOSET_PHOTO,
                'task_id' => $event->task->id, // 'parent_task_id
                'state_code' => States::STATE_INIT,

            ]);

            DownloadPhotoJob::dispatch($task)->onQueue(FlickrService::QUEUE_NAME);
        });
    }

    public function onPhotosetPhotoDownloadCompletedEvent(PhotosetPhotoDownloadCompletedEvent $event)
    {
        $parentTask = $event->task->parentTask;
        $totalPhotos = $parentTask->payload['photos'];
        $downloadedPhotos = $parentTask
            ->subTasks()
            ->where('task', FlickrService::TASK_DOWNLOAD_PHOTOSET_PHOTO)
            ->where('state_code', States::STATE_COMPLETED)
            ->count();

        if ($totalPhotos === $downloadedPhotos) {
            $parentTask->update([
                'state_code' => States::STATE_COMPLETED
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
            PhotosetReadyForDownload::class,
            [self::class, 'onPhotosetReadyForDownload']
        );

        $events->listen(
            PhotosetPhotoDownloadCompletedEvent::class,
            [self::class, 'onPhotosetPhotoDownloadCompletedEvent']
        );
    }
}
