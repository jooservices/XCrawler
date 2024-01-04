<?php

namespace App\Modules\Core\Tests\Unit\Services;

use App\Modules\Core\Events\TaskCreatedEvent;
use App\Modules\Core\Models\Task;
use App\Modules\Core\Services\States;
use App\Modules\Core\Services\TaskService;
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

        $contact = FlickrContact::factory()->create();
        $task = $this->service->create($contact, 'test');

        $this->assertEquals('test', $task->task);
        Event::assertDispatched(TaskCreatedEvent::class);
    }

    public function testGetTasks()
    {
        $contact = FlickrContact::factory()->create();
        $this->service->create($contact, 'test');
        $task = $contact->tasks()
            ->where('task', 'test')
            ->first();

        $tasks = $this->service->tasks('test', 1);
        $this->assertCount(1, $tasks);
        $this->assertEquals(States::STATE_IN_PROGRESS, $task->fresh()->state_code);
    }
}
