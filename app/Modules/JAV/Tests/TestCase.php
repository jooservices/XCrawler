<?php

namespace App\Modules\JAV\Tests;

class TestCase extends \Tests\TestCase
{
    public function getFixtures(string $path)
    {
        return file_get_contents(__DIR__ . '/Fixtures/' . $path);
    }
}
