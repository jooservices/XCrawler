<?php

namespace App\Modules\JAV\Tests;

use App\Modules\Client\Services\Factory;
use GuzzleHttp\Client;
use Mockery;
use Mockery\MockInterface;

class TestCase extends \Tests\TestCase
{
    public function getFixtures(string $path)
    {
        return file_get_contents(__DIR__ . '/Fixtures/' . $path);
    }
}
