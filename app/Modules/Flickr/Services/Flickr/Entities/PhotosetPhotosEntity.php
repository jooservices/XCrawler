<?php

namespace App\Modules\Flickr\Services\Flickr\Entities;

class PhotosetPhotosEntity extends AbstractBaseListEntity
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
