<?php

namespace App\Modules\Client\Services\Flickr\Adapters;

use App\Modules\Client\Services\Flickr\Adapters\Traits\HasList;

class People extends BaseAdapter implements ListInterface
{
    use HasList;

    public const PER_PAGE = 500;

    protected string $entities = 'photos';

    protected string $entity = 'photo';

    protected string $getListMethod = 'flickr.people.getPhotos';
}
