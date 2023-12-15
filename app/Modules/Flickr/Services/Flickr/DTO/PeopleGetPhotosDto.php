<?php

namespace App\Modules\Flickr\Services\Flickr\DTO;

class PeopleGetPhotosDto extends AbstractBaseListDto
{
    public function getEntities(): string
    {
        return 'photos';
    }

    public function getEntity(): string
    {
        return 'photo';
    }
}
