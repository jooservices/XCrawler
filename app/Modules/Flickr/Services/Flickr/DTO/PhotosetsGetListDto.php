<?php

namespace App\Modules\Flickr\Services\Flickr\DTO;

class PhotosetsGetListDto extends AbstractBaseListDto
{
    public function getEntities(): string
    {
        return 'photosets';
    }

    public function getEntity(): string
    {
        return 'photoset';
    }
}
