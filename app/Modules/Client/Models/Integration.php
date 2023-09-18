<?php

namespace App\Modules\Client\Models;

use App\Modules\Client\Database\factories\IntegrationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Builder;
use Jenssegers\Mongodb\Eloquent\Model;

class Integration extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';

    protected $collection = 'integrations';

    protected $fillable = [
        'service',
        'token',
        'token_secret',
        'data'
    ];

    protected $casts = [
        'service' => 'string',
        'token' => 'string',
        'token_secret' => 'string',
        'data' => 'array',
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
