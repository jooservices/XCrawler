<?php

namespace App\Modules\Core\Tests;

use App\Modules\Core\Models\Setting;
use Tests\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Setting::truncate();
    }
}
