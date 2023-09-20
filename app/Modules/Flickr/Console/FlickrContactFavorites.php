<?php

namespace App\Modules\Flickr\Console;

use App\Modules\Core\Services\States;
use App\Modules\Flickr\Jobs\FlickrFavorites;
use App\Modules\Flickr\Repositories\ContactRepository;
use Illuminate\Console\Command;

class FlickrContactFavorites extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'flickr:contact-favorites';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch all favorite photo of a user.';

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
        $contact = app(ContactRepository::class)->getContactForFavorites()->first();

        $contact->update([
            'favorites_state_code' => States::STATE_IN_PROGRESS
        ]);

        FlickrFavorites::dispatch($contact->nsid)->onQueue('flickr');
    }
}
