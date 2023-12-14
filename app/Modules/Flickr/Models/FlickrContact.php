<?php

namespace App\Modules\Flickr\Models;

use App\Modules\Core\Models\Task;
use App\Modules\Core\Models\TaskInterface;
use App\Modules\Core\Models\Traits\HasStates;
use App\Modules\Core\Models\Traits\HasTasks;
use App\Modules\Core\Models\Traits\HasUuid;
use App\Modules\Core\Services\States;
use App\Modules\Flickr\Database\factories\ContactFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $nsid
 * @property string $username
 * @property string $realname
 */
class FlickrContact extends Model implements TaskInterface
{
    use HasUuid;
    use HasTasks;
    use HasFactory;

    protected $fillable = [
        'nsid',
        'username',
        'realname',
        'friend',
        'family',
        'ignored',
        'rev_ignored',
        'iconserver',
        'iconfarm',
        'path_alias',
        'has_stats',
        'gender',
        'location',
        'description',
        'photosurl',
        'profileurl',
        'mobileurl',
    ];

    protected $casts = [
        'friend' => 'boolean',
        'family' => 'boolean',
        'ignored' => 'boolean',
        'rev_ignored' => 'boolean',
    ];

    protected $table = 'flickr_contacts';

    protected static function newFactory(): ContactFactory
    {
        return ContactFactory::new();
    }

    public function photos(): HasMany
    {
        return $this->hasMany(FlickrPhoto::class, 'owner', 'nsid');
    }
}
