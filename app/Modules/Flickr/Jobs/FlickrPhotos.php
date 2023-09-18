<?php

namespace App\Modules\Flickr\Jobs;

use App\Modules\Client\Services\FlickrService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FlickrPhotos implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public string $nsid, public int $page = 1)
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(FlickrService $flickrService)
    {
        $peopleService = $flickrService->people;
        $peopleService->getList([
            'user_id' => $this->nsid,
            'page' => $this->page
        ])->each(function ($photo) {
            \App\Modules\Flickr\Models\FlickrPhotos::updateOrCreate(
                [
                    'owner' => $photo['owner'],
                    'id' => $photo['id']
                ],
                $photo
            );
        });

        if ($this->page === $peopleService->totalPages()) {
            return;
        }

        self::dispatch($this->page + 1);
    }
}
