<?php

namespace App\Modules\Core\Tests\Unit\Services;

use App\Modules\Core\Facades\Setting;
use App\Modules\Core\Tests\TestCase;

class SettingTest extends TestCase
{
    public function testRemember()
    {
        $value = Setting::remember('home', 'title', function () {
            return 'Test';
        });

        $this->assertEquals('Test', $value);
        $this->assertEquals('Test', Setting::remember('home', 'title', fn() => 'Test2'));
        $this->assertEquals('Test', Setting::get('home', 'title'));

        Setting::forget('home', 'title');
        $this->assertNull(Setting::get('home', 'title'));
    }

    public function testGetArray()
    {
        Setting::setArray('home', 'array', ['test' => 'test-value']);
        $this->assertEquals(['test' => 'test-value'], Setting::getArray('home', 'array'));
    }

    public function testSetInt()
    {
        Setting::setInt('home', 'int', false);
        $this->assertEquals(0, Setting::getInt('home', 'int'));
    }

    public function testHas()
    {
        $this->assertFalse(Setting::has('home', $this->faker->title));
    }
}
