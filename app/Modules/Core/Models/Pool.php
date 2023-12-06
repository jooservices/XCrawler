<?php

namespace App\Modules\Core\Models;

use App\Modules\Core\Database\factories\PoolFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;

class Pool extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'pool';

    protected $fillable = [
        'job',
        'queue',
        'payload',
        'state_code',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    protected static function newFactory(): PoolFactory
    {
        return PoolFactory::new();
    }
}
