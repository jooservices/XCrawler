<?php

namespace App\Modules\JAV\Tests;

use App\Modules\JAV\Models\Onejav;
use App\Modules\Core\Tests\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Onejav::truncate();
    }
    public function getFixtures(string $path): string
    {
        return file_get_contents(__DIR__ . '/Fixtures/' . $path);
    }
}
