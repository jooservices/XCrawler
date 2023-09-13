<?php

namespace App\Modules\Core\Tests\Unit\Services;

use App\Modules\Core\Models\Setting;
use Tests\TestCase;

class SettingTest extends TestCase
{
    public function testRemember()
    {
        Setting::truncate();

        $value = \App\Modules\Core\Facades\Setting::remember('home', 'title', function () {
            return 'Test';
        });

        $this->assertEquals('Test', $value);
        $this->assertEquals('Test', \App\Modules\Core\Facades\Setting::get('home', 'title'));

        \App\Modules\Core\Facades\Setting::forget('home', 'title');
        $this->assertNull(\App\Modules\Core\Facades\Setting::get('home', 'title'));
    }

    public function testGetArray()
    {
        Setting::truncate();

        \App\Modules\Core\Facades\Setting::setArray('home', 'array', ['test' => 'test-value']);
        $this->assertEquals(['test' => 'test-value'], \App\Modules\Core\Facades\Setting::getArray('home', 'array'));
    }
}
