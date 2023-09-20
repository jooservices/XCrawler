<?php

namespace App\Modules\Flickr\Repositories;

use App\Modules\Core\Repositories\CrudRepository;
use App\Modules\Flickr\Models\FlickrContacts;

class ContactRepository extends CrudRepository
{
    public function __construct()
    {
        $this->setModel(app(FlickrContacts::class));
    }

    public function create(array $attributes): FlickrContacts
    {
        return FlickrContacts::updateOrCreate(
            [
                'nsid' => $attributes['nsid']
            ],
            $attributes
        );
    }
}
