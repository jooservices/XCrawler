<?php

namespace App\Modules\Flickr\Tests\Unit\Models;

use App\Modules\Core\Models\Task;
use App\Modules\Core\StateMachine\Task\InitState;
use App\Modules\Flickr\Models\FlickrContact;
use App\Modules\Flickr\Models\FlickrPhoto;
use App\Modules\Flickr\Tests\TestCase;

class ContactTest extends TestCase
{
    public function testFactory()
    {
        $model = FlickrContact::factory()->create();
        $this->assertInstanceOf(FlickrContact::class, $model);
    }

    public function testPhotosRelationship()
    {
        $contact = FlickrContact::factory()->create();
        $model = $contact->photos()->create([
            'farm' => 1,
            'isfamily' => false,
            'isfriend' => false,
            'ispublic' => false,
            'secret' => $this->faker->uuid,
            'server' => $this->faker->uuid,
            'title' => $this->faker->sentence,
        ]);

        $this->assertInstanceOf(FlickrPhoto::class, $model);
    }

    public function testTaskRelationship()
    {
        $contact = FlickrContact::factory()->create();
        $task = $contact->tasks()->create([
            'task' => $this->faker->text,
        ]);

        $this->assertInstanceOf(Task::class, $task);
        $this->assertEquals(InitState::class, $task->refresh()->state_code);
    }
}
