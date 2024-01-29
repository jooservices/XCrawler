<?php

namespace App\Modules\Core\Tests\God;

use App\Modules\Core\God\Generator;
use App\Modules\Core\Tests\TestCase;

class TestGenerator extends TestCase
{
    public function testGenerator()
    {
        $god = app(Generator::class);
        $integration = $god->integration()->isPrimary(false)->get();
        $this->assertFalse($integration->is_primary);
    }
}
