<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ImageUpload
 * @package App
 */
class ImageUpload extends Model
{
    /**
     * table name
     * @var string
     */
    protected $table = 'uploaded_images';
    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var array
     */
    protected $fillable = [
        'image_name',
        'size',
        'cdn_url'
    ];
}
