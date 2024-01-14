<?php

namespace App\Modules\Core\Tests\Unit\Services;

use App\Modules\Core\Events\TaskCreatedEvent;
use App\Modules\Core\Services\TaskService;
use App\Modules\Core\StateMachine\Task\InProgressState;
use App\Modules\Core\Tests\TestCase;
use App\Modules\Flickr\Models\FlickrContact;
use Illuminate\Support\Facades\Event;

class TaskServiceTest extends TestCase
{
    private TaskService $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = app(TaskService::class);
    }

    public function testCreateTask()
    {
        Event::fake(TaskCreatedEvent::class);

        $task = $this->service->create(FlickrContact::factory()->create(), 'test');

        $this->assertEquals('test', $task->task);
        Event::assertDispatched(TaskCreatedEvent::class);
    }

    public function testGetTasks()
    {
        $this->service->create($contact = FlickrContact::factory()->create(), 'test');
        $task = $contact->tasks()
            ->where('task', 'test')
            ->first();

        $this->assertCount(1, $this->service->tasks('test'));
        $this->assertEquals(InProgressState::class, $task->fresh()->state_code);
    }
}
