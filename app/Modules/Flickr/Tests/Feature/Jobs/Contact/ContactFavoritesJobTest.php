<?php

namespace App\Modules\Flickr\Tests\Feature\Jobs\Contact;

use App\Modules\Core\Events\RecurredTaskEvent;
use App\Modules\Core\StateMachine\Task\CompletedState;
use App\Modules\Core\StateMachine\Task\FailedState;
use App\Modules\Core\StateMachine\Task\InProgressState;
use App\Modules\Flickr\Events\ContactCreatedEvent;
use App\Modules\Flickr\Events\Exceptions\UserNotFoundEvent;
use App\Modules\Flickr\Exceptions\UserNotFoundException;
use App\Modules\Flickr\God\Providers\AbstractProvider;
use App\Modules\Flickr\Jobs\ContactFavoritesJob;
use App\Modules\Flickr\Services\FlickrContactService;
use App\Modules\Flickr\Services\TaskService;
use App\Modules\Flickr\Tests\TestCase;
use Exception;
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
        $contact = app(FlickrContactService::class)->create(['nsid' => AbstractProvider::NSID]);

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

        $contact = app(FlickrContactService::class)->create(['nsid' => AbstractProvider::NSID_USER_NOT_FOUND]);
        $this->assertEquals(count(TaskService::CONTACT_TASKS), $contact->refresh()->tasks->count());

        $task = $contact->refresh()->tasks()->where('task', TaskService::TASK_CONTACT_FAVORITES)->first();
        $task->transitionTo(InProgressState::class);

        try {
            ContactFavoritesJob::dispatch($this->integration, $task);
        } catch (Exception $e) {
            $this->assertInstanceOf(UserNotFoundException::class, $e);
        }

        // Tasks deleted
        $this->assertEquals(0, $contact->refresh()->tasks->count());
        // Contact soft deleted
        $this->assertTrue($contact->trashed());
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function testGetPeopleFavoritesWithUserNotFoundFakeEvent()
    {
        Event::fake(UserNotFoundEvent::class);
        $this->assertDatabaseCount('flickr_photos', 0);
        $this->assertDatabaseCount('flickr_contacts', 0);

        $contact = app(FlickrContactService::class)->create(['nsid' => AbstractProvider::NSID_USER_NOT_FOUND]);
        $this->assertEquals(count(TaskService::CONTACT_TASKS), $contact->refresh()->tasks->count());

        $task = $contact->refresh()->tasks()->where('task', TaskService::TASK_CONTACT_FAVORITES)->first();
        $task->transitionTo(InProgressState::class);

        try {
            ContactFavoritesJob::dispatch($this->integration, $task);
        } catch (Exception $e) {
            $this->assertInstanceOf(UserNotFoundException::class, $e);
        }

        Event::assertDispatched(UserNotFoundEvent::class, function ($event) use ($contact) {
            return $event->contact->is($contact);
        });

        $this->assertTrue($task->refresh()->isState(FailedState::class));
    }
}
