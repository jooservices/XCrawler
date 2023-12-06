<?php

namespace App\Modules\Core\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use App\Modules\Core\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Eloquent
{
    use HasFactory;
    use HasUuid;

    protected $connection = 'mongodb';
    protected $collection = 'tasks';

    protected $fillable = [
        'uuid',
        'model_type',
        'model_id',
        'task',
        'state_code',
    ];

    public function model(): MorphTo
    {
        return $this->morphTo('model');
    }
}
