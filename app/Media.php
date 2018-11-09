<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    public const TYPE_IMAGE = 'image';
    public const TYPE_ICON = 'icon';
    public const TYPE_AUDIO = 'audio';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The custom attributes that are automatically appended to the model.
     *
     * @var array
     */
    protected $appends = ['path', 'small_path', 'icon_path'];

    /**
     * The attributes that should be automatically cast.
     *
     * @var array
     */
    protected $casts = [
        'length' => 'float',
    ];

    /**
     * Defines the user relationship for who uploaded the media.
     *
     * @return void
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the media's full URL.
     *
     * @return void
     */
    public function getPathAttribute()
    {
        return config('filesystems.disks.s3.url') . $this->file;
    }

    /**
     * Get the media's full URL.
     *
     * @return void
     */
    public function getSmallPathAttribute()
    {
        return config('filesystems.disks.s3.url') . $this->modFilename($this->file, '_sm');
    }

    /**
     * Get the media's full URL.
     *
     * @return void
     */
    public function getIconPathAttribute()
    {
        return config('filesystems.disks.s3.url') . $this->modFilename($this->file, '_ico');
    }

    /**
     * Add string to the end of the filename.
     *
     * @param string $filename
     * @param string $mod
     * @return string
     */
    public function modFilename($filename, $mod)
    {
        return substr($filename, 0, strpos($filename, '.')) . $mod . substr($filename, strpos($filename, '.'));
    }
}
