<?php

namespace App\Modules\Flickr\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property string $album_id
 * @property int $flickr_photoset_id
 * @property string $title
 */
class GooglePhotoAlbum extends Model
{
    use HasFactory;

    protected $fillable = [
        'album_id',
        'flickr_photoset_id',
        'title',
    ];

    protected $table = 'flickr_google_photo_albums';
}
