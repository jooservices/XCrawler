<?php

namespace App\Modules\Client\Services;

use App\Modules\Client\Services\Flickr\Adapters\Contacts;
use App\Modules\Client\Services\Flickr\Adapters\People;
use Exception;

/**
* @property Contacts $contacts
 * @property People $people
 */
class FlickrService
{
    public function __get(string $name)
    {
        $adapter = 'App\\Modules\\Client\\Services\\Flickr\\Adapters\\' . ucfirst($name);

        if (class_exists($adapter)) {
            return app($adapter);
        }

        throw new Exception('Adapter not found');
    }
}
