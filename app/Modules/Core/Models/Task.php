<?php

namespace App\Modules\Core\Models;

use App\Modules\Core\Database\factories\TaskFactory;
use App\Modules\Core\Models\Traits\HasStates as HasStatesCover;
use App\Modules\Core\Models\Traits\HasUuid;
use App\Modules\Core\StateMachine\Task\CompletedState;
use App\Modules\Core\StateMachine\Task\TaskState;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\ModelStates\HasStates;
use Spatie\ModelStates\State;

/**
 * @property int $id
 * @property Task $parentTask
 * @property string $uuid
 * @property string $model_type
 * @property int $model_id
 * @property string $task
 * @property State $state_code
 * @property array $payload
 */
class Task extends Model
{
    use HasFactory;
    use HasUuid;
    use HasStates;
    use HasStatesCover;

    protected $table = 'tasks';

    protected $fillable = [
        'uuid',
        'task_id',
        'model_type',
        'model_id',
        'task',
        'payload',
    ];

    protected $casts = [
        'uuid' => 'string',
        'task_id' => 'integer',
        'model_type' => 'string',
        'model_id' => 'integer',
        'task' => 'string',
        'payload' => 'array',
        'state_code' => TaskState::class
    ];

    protected static function newFactory(): TaskFactory
    {
        return TaskFactory::new();
    }

    public function model(): MorphTo
    {
        return $this->morphTo('model');
    }

    public function isTask(string $task): bool
    {
        return $this->task === $task;
    }

    public function subTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'task_id');
    }

    public function isSubTasksCompleted(): bool
    {
        return $this->subTasks()->count() === $this->subTasks()->where('state_code', CompletedState::class)->count();
    }

    public function parentTask(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function updatePayload(array $payload): self
    {
        $this->update([
            'payload' => array_merge($this->payload, $payload)
        ]);

        return $this;
    }
}
