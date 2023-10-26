<?php

namespace App\Modules\Flickr\Jobs;

use App\Modules\Client\Services\FlickrManager;
use App\Modules\Flickr\Repositories\ContactRepository;
use App\Modules\Flickr\Services\FlickrService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FlickrContacts implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $tries = 10;

    public $timeout = 60;

    public $retryAfter = 120;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public int $page = 1)
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(FlickrService $flickrService)
    {
        $flickrService->contacts($this->page);
    }
}
