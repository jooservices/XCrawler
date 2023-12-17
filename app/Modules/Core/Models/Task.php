<?php

namespace App\Modules\Core\Models;

use App\Modules\Core\Models\Traits\HasStates;
use App\Modules\Core\Services\States;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Modules\Core\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
        'model_type',
        'model_id',
        'task',
    ];

    protected $casts = [
        'uuid' => 'string',
        'model_type' => 'string',
        'model_id' => 'integer',
        'task' => 'string',
    ];

    public function model(): MorphTo
    {
        return $this->morphTo('model');
    }
}
