<?php

namespace App\Modules\Client\Models;

use App\Modules\Client\Database\factories\IntegrationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Builder;
use Jenssegers\Mongodb\Eloquent\Model;

/**
 * @property string $service
 * @property string $key
 */
class Integration extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';

    protected $collection = 'integrations';

    protected $fillable = [
        'service',
        'key',
        'secret',
        'callback',
        'is_primary',
        'token',
        'token_secret',
        'data',
        'state_code',
    ];

    protected $casts = [
        'service' => 'string',
        'key' => 'string',
        'secret' => 'string',
        'callback' => 'string',
        'is_primary' => 'string',
        'token' => 'string',
        'token_secret' => 'string',
        'data' => 'array',
        'state_code' => 'string',
    ];

    protected static function newFactory()
    {
        return IntegrationFactory::new();
    }

    public function scopeByService(Builder $builder, string $service)
    {
        return $builder->where(compact('service'));
    }
}
