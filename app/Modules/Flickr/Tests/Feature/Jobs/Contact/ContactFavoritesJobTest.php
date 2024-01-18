<?php

namespace App\Modules\Flickr\Tests\Feature\Jobs\Contact;

use App\Modules\Core\StateMachine\Task\CompletedState;
use App\Modules\Core\StateMachine\Task\FailedState;
use App\Modules\Core\StateMachine\Task\InProgressState;
use App\Modules\Flickr\Events\ContactCreatedEvent;
use App\Modules\Flickr\Events\RecurredTaskEvent;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\FailedException;
use App\Modules\Flickr\Exceptions\UserNotFoundException;
use App\Modules\Flickr\Jobs\ContactFavoritesJob;
use App\Modules\Flickr\Services\FlickrContactService;
use App\Modules\Flickr\Services\TaskService;
use App\Modules\Flickr\Tests\TestCase;
use Illuminate\Support\Facades\Event;

class ContactFavoritesJobTest extends TestCase
{
    public function testGetPeopleFavorites()
    {
        $this->assertDatabaseCount('flickr_photos', 0);
        $this->assertDatabaseCount('flickr_contacts', 0);

        /**
         * This user have 1487 favorites.
         */
        $contact = app(FlickrContactService::class)->create(['nsid' => '94529704@N02']);

        $this->assertEquals(0, (int)$this->integration->refresh()->requested_times);
        $this->assertEquals(count(TaskService::CONTACT_TASKS), $contact->refresh()->tasks->count());

        Event::fake([
            ContactCreatedEvent::class,
            RecurredTaskEvent::class
        ]);

        $task = $contact->refresh()->tasks()->where('task', TaskService::TASK_CONTACT_FAVORITES)->first();
        $task->transitionTo(InProgressState::class);

        ContactFavoritesJob::dispatch($this->integration, $task);

        Event::assertDispatchedTimes(ContactCreatedEvent::class, 350);
        Event::assertDispatchedTimes(RecurredTaskEvent::class, 3);

        $this->assertDatabaseCount('flickr_photos', 1487);
        $this->assertDatabaseCount('flickr_contacts', 351);

        $this->assertEquals(4, (int)$task->refresh()->payload['page']);
        $this->assertEquals(CompletedState::class, $task->state_code);
    }

    public function testGetPeopleFavoritesWithUserNotFound()
    {
        $this->assertDatabaseCount('flickr_photos', 0);
        $this->assertDatabaseCount('flickr_contacts', 0);

        $contact = app(FlickrContactService::class)->create(['nsid' => '64994773@N03']);
        $this->assertEquals(count(TaskService::CONTACT_TASKS), $contact->refresh()->tasks->count());

        $task = $contact->refresh()->tasks()->where('task', TaskService::TASK_CONTACT_FAVORITES)->first();
        $task->transitionTo(InProgressState::class);

        $this->expectException(UserNotFoundException::class);
        ContactFavoritesJob::dispatch($this->integration, $task);

        // Tasks deleted
        $this->assertEquals(0, $contact->refresh()->tasks->count());
        $this->assertTrue($task->refresh()->isState(FailedState::class));
    }
}
