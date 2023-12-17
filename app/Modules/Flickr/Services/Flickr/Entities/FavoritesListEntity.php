<?php

namespace App\Modules\Flickr\Services\Flickr\Entities;

class FavoritesListEntity extends AbstractBaseListEntity
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
