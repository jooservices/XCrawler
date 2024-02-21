<?php

namespace App\Modules\Core\Entities;

class VideoCodecEntity extends BaseEntity
{
    protected array $fields = [
        'codec_name',
        'codec_long_name',
        'profile',
        'codec_type',
        'codec_tag_string',
        'width',
        'height',
        'coded_width',
        'coded_height',
        'sample_aspect_ratio',
        'display_aspect_ratio',
        'duration',
        'bit_rate',
        'bits_per_raw_sample',
        'nb_frames',
    ];
}
