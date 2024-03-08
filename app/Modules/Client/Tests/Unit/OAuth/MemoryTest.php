<?php

namespace App\Modules\Client\Tests\Unit\OAuth;

use App\Modules\Client\OAuth\Exceptions\TokenNotFoundException;
use App\Modules\Client\OAuth\OAuth1\Token\TokenInterface;
use App\Modules\Client\OAuth\Storage\Memory;
use App\Modules\Client\Tests\TestCase;

class MemoryTest extends TestCase
{
    public function testRetrieveNotExistsAccessToken(): void
    {
        $storage = new Memory();
        $this->expectException(TokenNotFoundException::class);
        $storage->retrieveAccessToken('service');
        $this->assertFalse($storage->hasAccessToken('service'));
    }

    public function testStoreAndRetrieveAccessToken(): void
    {
        $storage = new Memory();
        $token = $this->createMock(TokenInterface::class);
        $storage->storeAccessToken('service', $token);
        $this->assertTrue($storage->hasAccessToken('service'));
        $this->assertSame($token, $storage->retrieveAccessToken('service'));

        $storage->clearToken('service');
        $this->assertFalse($storage->hasAccessToken('service'));
    }
}
