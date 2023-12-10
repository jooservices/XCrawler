<?php

namespace App\Modules\Client\Tests\Unit;

use App\Modules\Client\Tests\TestCase;
use App\Modules\Client\Uri\Uri;
use InvalidArgumentException;

class UriTest extends TestCase
{
    public function testInvalidArgumentException()
    {
        $this->expectException(InvalidArgumentException::class);
        new Uri('example.com');
    }

    public function testUriPort()
    {
        $uri = new Uri('https://example.com:8080');

        $this->assertTrue($uri->hasExplicitPortSpecified());

        $uri = new Uri('https://example.com');
        $this->assertFalse($uri->hasExplicitPortSpecified());
    }
}
