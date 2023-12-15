<?php

namespace App\Modules\Flickr\Services\Flickr\DTO;

class ContactsGetListDto extends AbstractBaseListDto
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
