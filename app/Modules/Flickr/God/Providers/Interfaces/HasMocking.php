<?php

namespace App\Modules\Flickr\God\Providers\Interfaces;

use Mockery\MockInterface;

interface HasMocking
{
    public function setMocking(MockInterface $mock): self;

    public function getMocking(): MockInterface;
}
