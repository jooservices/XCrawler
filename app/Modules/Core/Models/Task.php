<?php

namespace App\Modules\Core\Models;

use App\Modules\Core\Models\Traits\HasStates;
use App\Modules\Core\Models\Traits\HasUuid;
use App\Modules\Core\Services\States;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property string $uuid
 * @property string $model_type
 * @property int $model_id
 * @property string $task
 * @property string $state_code
 */
class Task extends Model
{
    use HasStates;
    use HasFactory;
    use HasUuid;

    public const STATE_INIT = States::STATE_INIT;
    public const STATE_IN_PROGRESS = States::STATE_IN_PROGRESS;

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
        'payload' => 'array'
    ];

    public function model(): MorphTo
    {
        return $this->morphTo('model');
    }

    public function subTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'task_id');
    }

    public function parentTask(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }
}
