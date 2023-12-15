<?php

namespace App\Modules\Flickr\Services\Flickr\DTO;

class PhotosetsGetPhotosDto extends AbstractBaseListDto
{
    public function getEntities(): string
    {
        return 'photoset';
    }

    public function getEntity(): string
    {
        return 'photo';
    }
}
