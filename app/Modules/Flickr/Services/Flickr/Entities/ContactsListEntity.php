<?php

namespace App\Modules\Flickr\Services\Flickr\Entities;

class ContactsListEntity extends AbstractBaseListEntity
{
    public function getEntities(): string
    {
        return 'contacts';
    }

    public function getEntity(): string
    {
        return 'contact';
    }
}
