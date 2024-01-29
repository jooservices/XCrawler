<?php

namespace App\Modules\Core\Tests;

use App\Modules\Client\Models\Integration;
use App\Modules\Client\Models\RequestLog;
use App\Modules\Core\Models\Setting;
use Tests\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Setting::truncate();
        Integration::truncate();
        RequestLog::truncate();
    }
}
