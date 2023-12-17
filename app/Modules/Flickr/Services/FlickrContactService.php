<?php

namespace App\Modules\Flickr\Services;

use App\Modules\Flickr\Events\ContactCreatedEvent;
use App\Modules\Flickr\Models\FlickrContact;
use App\Modules\Flickr\Repositories\ContactRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class FlickrContactService
{
    public function __construct(private readonly ContactRepository $repository)
    {
    }

    public function create(array $contact): FlickrContact
    {
        $contact = $this->repository->create($contact);

        if ($contact->wasRecentlyCreated) {
            Event::dispatch(new ContactCreatedEvent($contact));
        }

        return $contact;
    }

    public function addPhotos(Collection $photos)
    {
        /**
         * Need careful here. We won't create duplicate photos
         * @TODO Multi insert in same query
         */
        $contactNsids = $photos->pluck('owner')->unique()->toArray();
        $contacts = FlickrContact::whereIn('nsid', $contactNsids)->get()->keyBy('nsid');
        $columns = DB::getSchemaBuilder()->getColumnListing('flickr_photos');

        $photos->each(function ($photo) use ($contacts, $columns) {
            $contact = $contacts->get($photo['owner']);
            if (!$contact) {
                $contact = $this->create(['nsid' => $photo['owner']]);
            }

            $colDiff = array_keys(array_diff(array_keys($photo), $columns));
            foreach ($colDiff as $col) {
                unset($photo[$col]);
            }

            $contact->photos()->updateOrCreate(
                [
                    'owner' => $photo['owner'],
                    'id' => $photo['id']
                ],
                $photo
            );
        });
    }
}
