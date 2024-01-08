<?php

namespace App\Modules\Core\Tests\Unit\Services;

use App\Modules\Core\Services\FileManager;
use App\Modules\Core\Tests\TestCase;
use Exception;

class FileManagerTest extends TestCase
{
    public function testInvalidUrl()
    {
        $this->expectException(Exception::class);
        app(FileManager::class)->download('invalid-url');
    }
}
