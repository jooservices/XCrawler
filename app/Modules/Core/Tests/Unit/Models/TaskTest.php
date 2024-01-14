<?php

namespace App\Modules\Core\Tests\Unit\Models;

use App\Modules\Core\Models\Task;
use App\Modules\Core\StateMachine\Task\InitState;
use App\Modules\Core\Tests\TestCase;

class TaskTest extends TestCase
{
    public function testCreate()
    {
        $task = Task::create([
            'model_type' => 'App\User',
            'model_id' => 1,
            'task' => 'test'
        ]);

        $this->assertInstanceOf(Task::class, $task);
        $this->assertEquals(InitState::class, $task->refresh()->state_code);
    }
}
