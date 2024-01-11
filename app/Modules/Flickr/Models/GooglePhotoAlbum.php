<?php

namespace App\Modules\Flickr\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GooglePhotoAlbum extends Model
{
    use HasFactory;

    protected $fillable = [
        'album_id',
        'flickr_photoset_id',
        'title',
    ];

    protected $table = 'flickr_google_photo_albums';

    protected static function newFactory()
    {
        return \App\Modules\Flickr\Database\factories\GooglePhotoAlbumFactory::new();
    }
}
