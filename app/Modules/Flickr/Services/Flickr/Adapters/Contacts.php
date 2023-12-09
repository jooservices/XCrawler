<?php

namespace App\Modules\Flickr\Services\Flickr\Adapters;

use App\Modules\Flickr\Services\Flickr\Adapters\Traits\HasList;

class Contacts extends BaseAdapter implements ListInterface
{
    use HasList;

    public const PER_PAGE = 1000;

    protected string $entity = 'contact';
    protected string $entities = 'contacts';

    protected string $getListMethod = 'flickr.contacts.getList';
}