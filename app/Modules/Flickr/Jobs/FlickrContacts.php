<?php

namespace App\Modules\Flickr\Jobs;

use App\Modules\Client\Services\FlickrService;
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

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public int $page = 1)
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
        $contactsService = $flickrService->contacts;
        $contactsService->getList(['page' => $this->page])->each(function ($contact) {
            \App\Modules\Flickr\Models\FlickrContacts::updateOrCreate(
                [
                    'nsid' => $contact['nsid']
                ],
                [
                    'nsid' => $contact['nsid'],
                    'username' => $contact['username'],
                    'realname' => $contact['realname'],
                    'friend' => $contact['friend'],
                    'family' => $contact['family'],
                    'ignored' => $contact['ignored'],
                    'iconserver' => $contact['iconserver'],
                    'iconfarm' => $contact['iconfarm'],
                    'path_alias' => $contact['path_alias'],
                ]
            );
        });

        if ($this->page === $contactsService->totalPages()) {
            return;
        }

        FlickrContacts::dispatch($this->page + 1);
    }
}
