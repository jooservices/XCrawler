<?php

namespace App\Modules\Flickr\Console;

use App\Modules\Core\Services\States;
use App\Modules\Flickr\Jobs\FlickrPhotos;
use App\Modules\Flickr\Repositories\ContactRepository;
use Illuminate\Console\Command;

class FlickrPeoplePhotos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'flickr:people-photos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch all photos of a user.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        /**
         * @var \App\Modules\Flickr\Models\FlickrContacts $contact
         */
        $contact = app(ContactRepository::class)->getContactsForPhotos()->first();

        $contact->update([
            'state_code' => States::STATE_IN_PROGRESS
        ]);

        FlickrPhotos::dispatch($contact->nsid)->onQueue('flickr');
    }
}
