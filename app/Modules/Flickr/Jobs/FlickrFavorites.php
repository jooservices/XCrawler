<?php

namespace App\Modules\Flickr\Jobs;

use App\Modules\Client\Services\FlickrService;
use App\Modules\Flickr\Models\FlickrContacts as FlickrContactsModel;
use App\Modules\Flickr\Models\FlickrPhotos as FlickrPhotosModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FlickrFavorites implements ShouldQueue
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
        $adapter = $flickrService->favorites;
        $adapter->getList([
            'user_id' => $this->nsid,
            'page' => $this->page
        ])->each(function ($photo) {
            unset($photo['date_faved']);
            FlickrPhotosModel::updateOrCreate(
                [
                    'owner' => $photo['owner'],
                    'id' => $photo['id']
                ],
                $photo
            );
            FlickrContactsModel::firstOrCreate([
                'nsid' => $photo['owner']
            ]);
        });

        if ($this->page === $adapter->totalPages()) {
            FlickrContactsModel::where('nsid', $this->nsid)->update([
                'favorites_state_code' => 'COMPLETED'
            ]);
            return;
        }

        FlickrContactsModel::where('nsid', $this->nsid)->update([
            'favorites_state_code' => 'RECURSIVE'
        ]);

        self::dispatch($this->nsid, $this->page + 1);
    }
}
