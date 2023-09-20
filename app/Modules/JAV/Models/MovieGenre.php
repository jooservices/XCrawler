<?php

namespace App\Modules\JAV\Models;

use App\Modules\Core\Models\Traits\HasUuid;
use App\Modules\JAV\Database\factories\MovieGenreFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovieGenre extends Model
{
    use HasFactory;
    use HasUuid;

    protected $table = 'genres';

    protected $fillable = [
        'id',
        'uuid',
        'name'
    ];

    protected static function newFactory()
    {
        return MovieGenreFactory::new();
    }
}
