<?php

namespace App\Modules\Flickr\Models;

use App\Modules\Core\Models\TaskInterface;
use App\Modules\Core\Models\Traits\HasTasks;
use App\Modules\Core\Models\Traits\HasUuid;
use App\Modules\Flickr\Database\factories\ContactFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $nsid
 * @property string $username
 * @property string $realname
 * @property bool $friend
 * @property bool $family
 * @property bool $ignored
 * @property bool $rev_ignored
 * @property int $iconserver
 * @property int $iconfarm
 * @property string $path_alias
 * @property bool $has_stats
 * @property string $gender
 * @property string $location
 * @property string $description
 * @property string $photosurl
 * @property string $profileurl
 * @property string $mobileurl
 * @property FlickrPhoto[] $photos
 * @property FlickrPhotoset[] $photosets
 */
class FlickrContact extends Model implements TaskInterface
{
    use HasUuid;
    use HasTasks;
    use HasFactory;
    use SoftDeletes;

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
        'nsid' => 'string',
        'username' => 'string',
        'realname' => 'string',
        'friend' => 'boolean',
        'family' => 'boolean',
        'ignored' => 'boolean',
        'rev_ignored' => 'boolean',
        'iconserver' => 'integer',
        'iconfarm' => 'integer',
        'path_alias' => 'string',
        'has_stats' => 'boolean',
        'gender' => 'string',
        'location' => 'string',
        'description' => 'string',
        'photosurl' => 'string',
        'profileurl' => 'string',
        'mobileurl' => 'string',
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

    public function photosets(): HasMany
    {
        return $this->hasMany(FlickrPhotoset::class, 'owner', 'nsid');
    }
}
