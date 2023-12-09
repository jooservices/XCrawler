<?php

namespace App\Modules\Client\Tests\Unit\OAuth;

use App\Modules\Client\Uri\Uri;
use Tests\TestCase;

class UriTest extends TestCase
{
    public function testUriWithHttp()
    {
        $uri = new Uri('http://example.com');

        $this->assertEquals('http', $uri->getScheme());
        $this->assertEquals('example.com', $uri->getHost());
        $this->assertEquals('/', $uri->getPath());
        $this->assertEquals('', $uri->getQuery());
        $this->assertEquals('', $uri->getFragment());
        $this->assertEquals(80, $uri->getPort());
        $this->assertEquals('http://example.com', (string)$uri);
    }

    public function testUriWithHttps()
    {
        $uri = new Uri('https://example.com');

        $this->assertEquals('https', $uri->getScheme());
        $this->assertEquals('example.com', $uri->getHost());
        $this->assertEquals('/', $uri->getPath());
        $this->assertEquals('', $uri->getQuery());
        $this->assertEquals('', $uri->getFragment());
        $this->assertEquals(443, $uri->getPort());
        $this->assertEquals('https://example.com', (string)$uri);
    }
}
