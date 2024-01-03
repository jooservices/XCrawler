<?php

namespace App\Modules\Flickr\Events;

use App\Modules\Core\Models\Task;
use Illuminate\Queue\SerializesModels;

class PhotosetPhotoDownloadCompletedEvent
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(public Task $task)
    {
        //
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}
