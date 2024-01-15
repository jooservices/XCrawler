<?php

namespace App\Modules\Flickr\Tests\Unit\Events;

use App\Modules\Core\Models\Task;
use App\Modules\Core\StateMachine\Task\CompletedState;
use App\Modules\Core\StateMachine\Task\InProgressState;
use App\Modules\Flickr\Events\FetchPhotosetPhotosCompletedEvent;
use App\Modules\Flickr\Events\PhotosetReadyForDownloadEvent;
use App\Modules\Flickr\Models\FlickrContact;
use App\Modules\Flickr\Models\FlickrPhotoset;
use App\Modules\Flickr\Services\TaskService;
use App\Modules\Flickr\Tests\TestCase;
use Illuminate\Support\Facades\Event;

class FetchPhotosetPhotosCompletedEventTest extends TestCase
{
    public function testHaveNoParentTask()
    {
        $photoset = FlickrPhotoset::factory()->create();
        /**
         * @var Task $task
         */
        $task = $photoset->tasks()->create([
            'task' => TaskService::TASK_CONTACT_PHOTOSETS,
        ]);
        $task->transitionTo(InProgressState::class);

        Event::dispatch(new FetchPhotosetPhotosCompletedEvent($task));

        $this->assertEquals(CompletedState::class, $task->fresh()->state_code);
    }

    public function testParentTask()
    {
        Event::fake([
            PhotosetReadyForDownloadEvent::class,
        ]);
        $photoset = FlickrPhotoset::factory()->create();
        $task = $photoset->tasks()->create([
            'task' => TaskService::TASK_DOWNLOAD_PHOTOSET,
        ]);
        $subTask = $task->subTasks()->create([
            'model_id' => FlickrContact::factory()->create()->id,
            'model_type' => FlickrContact::class,
            'task' => TaskService::TASK_CONTACT_PHOTOSETS,
        ]);
        $subTask->transitionTo(InProgressState::class);

        Event::dispatch(new FetchPhotosetPhotosCompletedEvent($subTask));
        Event::assertDispatched(PhotosetReadyForDownloadEvent::class, function ($event) use ($task) {
            return $event->task->is($task);
        });
    }
}
