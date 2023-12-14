<?php

namespace App\Modules\Client\Tests;

use App\Modules\Client\Models\Integration;
use App\Modules\Client\Models\RequestLog;
use App\Modules\Core\Tests\TestCase as BaseTestCase;

/**
 * @SuppressWarnings(PHPMD)
 */
class TestCase extends BaseTestCase
{
    protected Integration $integration;

    public function setUp(): void
    {
        parent::setUp();

        Integration::truncate();
        RequestLog::truncate();

        $this->integration = Integration::factory()->create();
    }

    public function getFixtures(string $path): string
    {
        return file_get_contents(__DIR__ . '/Fixtures/' . $path);
    }
}
