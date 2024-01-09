<?php

namespace App\Modules\Client\Tests\Unit;

use App\Modules\Client\Tests\TestCase;
use App\Modules\Client\Uri\Uri;
use App\Modules\Client\Uri\UriFactory;

class UriFactoryTest extends TestCase
{
    public function testCreateUrl()
    {
        $factory = app(UriFactory::class);
        $uri = $factory->createFromAbsolute('https://example.com');

        $this->assertUri($uri);
    }

    public function testCreateFromParts()
    {
        $factory = app(UriFactory::class);
        $uri = $factory->createFromParts('https', '', 'example.com', 443, '/', '');

        $this->assertUri($uri);
    }

    private function assertUri(Uri $uri)
    {
        $this->assertEquals('https', $uri->getScheme());
        $this->assertEquals('example.com', $uri->getHost());
        $this->assertEquals('/', $uri->getPath());
        $this->assertEquals('', $uri->getQuery());
        $this->assertEquals('', $uri->getFragment());
        $this->assertEquals('', $uri->getUserInfo());
        $this->assertEquals('example.com', $uri->getAuthority());
        $this->assertEquals(443, $uri->getPort());
        $this->assertEquals('https://example.com/', $uri->getAbsoluteUri());
    }
}
