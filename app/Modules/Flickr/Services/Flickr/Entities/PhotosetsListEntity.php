<?php

namespace App\Modules\Flickr\Services\Flickr\Entities;

class PhotosetsListEntity extends AbstractBaseListEntity
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
