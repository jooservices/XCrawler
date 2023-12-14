<?php

namespace App\Modules\Flickr\Services\Flickr\Adapters;

use App\Modules\Flickr\Services\Flickr\Adapters\Traits\HasList;

class Photosets extends BaseAdapter implements ListInterface
{
    use HasList;

    public const PER_PAGE = 500;

    protected string $entity = 'photo';
    protected string $entities = 'photoset';

    protected string $getListMethod = 'flickr.photosets.getPhotos';
}
