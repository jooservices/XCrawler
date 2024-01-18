<?php

namespace App\Modules\Client\Models;

use App\Modules\Client\Database\factories\IntegrationFactory;
use App\Modules\Client\OAuth\Credentials\CredentialsInterface;
use App\Modules\Client\StateMachine\Integration\IntegrationState;
use App\Modules\Core\Models\Traits\HasStates as HasStatesCover;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Builder;
use Jenssegers\Mongodb\Eloquent\Model;
use Spatie\ModelStates\HasStates;

/**
 * @property int $id
 * @property string $service
 * @property string $key
 * @property string $token
 * @property string $token_secret
 * @property string $refresh_token
 * @property string $name
 * @property string $secret
 * @property string $callback
 * @property bool $is_primary
 * @property IntegrationState $state_code
 */
class Integration extends Model implements CredentialsInterface
{
    use HasFactory;
    use HasStates;
    use HasStatesCover;

    protected $connection = 'mongodb';

    protected $collection = 'integrations';

    protected $fillable = [
        'service',
        'name',
        'key',
        'secret',
        'callback',
        'is_primary',
        'token',
        'token_secret',
        'refresh_token',
        'state_code',
        'requested_at',
        'requested_times'
    ];

    protected $casts = [
        'service' => 'string',
        'name' => 'string',
        'key' => 'string',
        'secret' => 'string',
        'callback' => 'string',
        'is_primary' => 'string',
        'token' => 'string',
        'token_secret' => 'string',
        'refresh_token' => 'string',
        'state_code' => IntegrationState::class,
        'requested_at' => 'datetime',
    ];

    protected static function newFactory()
    {
        return IntegrationFactory::new();
    }

    public function scopeByService(Builder $builder, string $service)
    {
        return $builder->where(compact('service'));
    }

    public function getCallbackUrl(): string
    {
        return $this->callback;
    }

    public function getConsumerId(): string
    {
        return $this->key;
    }

    public function getConsumerSecret(): string
    {
        return $this->secret;
    }

    public function getUid(): string
    {
        return (string) $this->id;
    }
}
