<?php

namespace App\Modules\Client\Exceptions;

use App\Modules\Client\Exceptions\Interfaces\InvalidUrlInterface;
use Exception;

class InvalidUrlException extends Exception implements InvalidUrlInterface
{
    public function __construct(private string $url)
    {
        parent::__construct('Invalid URL');
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
