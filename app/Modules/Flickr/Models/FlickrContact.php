<?php

namespace App\Modules\Flickr\Models;

use App\Modules\Core\Models\Task;
use App\Modules\Core\Models\Traits\HasStates;
use App\Modules\Core\Models\Traits\HasTasks;
use App\Modules\Flickr\Database\factories\ContactFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property string $nsid
 */
class FlickrContact extends Eloquent
{
    use HasStates;
    use HasTasks;
    use HasFactory;

    protected $guarded = [];

    protected $connection = 'mongodb';

    protected $collection = 'flickr_contacts';

    protected static function newFactory(): ContactFactory
    {
        return ContactFactory::new();
    }

    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'model');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(FlickrPhoto::class, 'owner', 'nsid');
    }
}
