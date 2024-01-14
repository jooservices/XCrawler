<?php

namespace App\Modules\Flickr\Tests\Feature\Jobs\Photoset;

use App\Modules\Core\StateMachine\Task\CompletedState;
use App\Modules\Core\StateMachine\Task\InProgressState;
use App\Modules\Core\StateMachine\Task\RecurringState;
use App\Modules\Flickr\Events\PhotosetCreatedEvent;
use App\Modules\Flickr\Events\RecurredTaskEvent;
use App\Modules\Flickr\Jobs\PhotosetsJob;
use App\Modules\Flickr\Services\FlickrContactService;
use App\Modules\Flickr\Services\TaskService;
use App\Modules\Flickr\Tests\TestCase;
use Illuminate\Support\Facades\Event;

class PhotosetsJobTest extends TestCase
{
    public function testGetList()
    {
        Event::fake(PhotosetCreatedEvent::class);

        $contact = app(FlickrContactService::class)->create([
            'nsid' => '99097633@N00'
        ]);

        $task = $contact->tasks()
            ->where('task', TaskService::TASK_CONTACT_PHOTOSETS)
            ->first();
        $task->state_code->transitionTo(InProgressState::class);

        PhotosetsJob::dispatch($this->integration, $task);

        $this->assertDatabaseCount('flickr_photosets', 47);
        $this->assertCount(47, $contact->refresh()->photosets);

        Event::assertDispatchedTimes(PhotosetCreatedEvent::class, 47);
        $this->assertEquals(CompletedState::class, $task->refresh()->state_code);
    }

    public function testGetListWithRecursive()
    {
        Event::fake([
            PhotosetCreatedEvent::class,
            RecurredTaskEvent::class
        ]);

        $contact = app(FlickrContactService::class)->create([
            'nsid' => '34938526@N02'
        ]);

        $task = $contact->tasks()
            ->where('task', TaskService::TASK_CONTACT_PHOTOSETS)
            ->first();
        $task->state_code->transitionTo(InProgressState::class);

        PhotosetsJob::dispatch($this->integration, $task);

        Event::assertDispatched(RecurredTaskEvent::class, function ($event) use ($task) {
            return $event->task->is($task)
                && $event->task->state_code->getValue() === RecurringState::class;
        });
        $this->assertEquals(CompletedState::class, $task->refresh()->state_code);

        $this->assertEquals(2, $task->refresh()->payload['page']);
    }
}
