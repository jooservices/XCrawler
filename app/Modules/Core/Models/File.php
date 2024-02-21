<?php

namespace App\Modules\Core\Models;

use App\Modules\Core\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\ModelStates\HasStates;

class File extends Model
{
    use HasFactory;
    use HasUuid;
    use HasStates;

    protected $fillable = [
        'uuid',
        'storage',
        'name',
        'path',
        'type',
        'extension',
        'format',
        'size',
        'hash',
        'ratio',
        'width',
        'height',
        'metadata',
    ];

    protected $casts = [
        'uuid' => 'string',
        'storage' => 'string',
        'name' => 'string',
        'path' => 'string',
        'type' => 'string',
        'extension' => 'string',
        'format' => 'string',
        'size' => 'integer',
        'hash' => 'string',
        'ratio' => 'float',
        'width' => 'integer',
        'height' => 'integer',
        'metadata' => 'array',
    ];
}
