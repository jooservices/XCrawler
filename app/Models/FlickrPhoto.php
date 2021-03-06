<?php

namespace App\Models;

use App\Models\Traits\HasStates;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property array $sizes
 */
class FlickrPhoto extends Model
{
    use HasFactory;
    use HasStates;

    public const STATE_INIT = 'FPIN';
    public const STATE_SIZE_FAILED = 'FPSF';
    public const STATE_SIZE_COMPLETED = 'FPSC';
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'id',
        'owner',
        'secret',
        'server',
        'farm',
        'title',
        'ispublic',
        'isfriend',
        'isfamily',
        'sizes',
        'state_code',
        'isprimary',
    ];

    protected $casts = [
        'owner' => 'string',
        'secret' => 'string',
        'server' => 'string',
        'farm' => 'string',
        'title' => 'string',
        'ispublic' => 'integer',
        'isfriend' => 'integer',
        'isfamily' => 'integer',
        'isprimary' => 'integer',
        'sizes' => 'array',
        'state_code' => 'string',
    ];

    public function hasSizes()
    {
        return !empty($this->sizes);
    }

    public function largestSize()
    {
        if (!$this->hasSizes()) {
            return null;
        }

        $sizes = collect($this->sizes['size']);
        $sizes = $sizes->sortBy(function ($size) {
            return $size['width'] + $size['height'];
        });

        return $sizes->last();
    }
}
