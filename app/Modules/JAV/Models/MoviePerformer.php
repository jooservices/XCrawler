<?php

namespace App\Modules\JAV\Models;

use App\Modules\Core\Models\Traits\HasUuid;
use App\Modules\JAV\Database\factories\MoviePerformerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MoviePerformer extends Model
{
    use HasFactory;
    use HasUuid;

    protected $table = 'performers';

    protected $fillable = [
        'id',
        'uuid',
        'name'
    ];

    protected static function newFactory()
    {
        return MoviePerformerFactory::new();
    }
}
