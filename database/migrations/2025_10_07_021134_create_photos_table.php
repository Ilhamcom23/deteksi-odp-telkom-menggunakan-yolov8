<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Photo extends Model
{
    use HasFactory;

    protected $primaryKey = 'photo_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'photo_id',
        'uploader',
        'file_url',
        'file_name',
        'mime_type',
        'file_size',
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'file_size' => 'float',
    ];

    public function detection(): HasOne
    {
        return $this->hasOne(OdpDetection::class, 'photo_id', 'photo_id');
    }

    // Accessor untuk file size formatted
    public function getFileSizeFormattedAttribute()
    {
        if (!$this->file_size) {
            return '0 KB';
        }

        $bytes = $this->file_size * 1024; // Convert KB back to bytes untuk formatting

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    // Accessor untuk nama file tanpa prefix timestamp
    public function getOriginalFileNameAttribute()
    {
        if (!$this->file_name) {
            return null;
        }

        // Hapus bagian timestamp dan random string di depan
        return preg_replace('/^\d+_[\w]+_/', '', $this->file_name);
    }

    // Method untuk mendapatkan tipe file yang lebih readable
    public function getFileTypeAttribute()
    {
        if (!$this->mime_type) {
            return 'Unknown';
        }

        $types = [
            'image/jpeg' => 'JPEG Image',
            'image/jpg' => 'JPG Image',
            'image/png' => 'PNG Image',
            'image/gif' => 'GIF Image',
            'image/webp' => 'WebP Image',
        ];

        return $types[$this->mime_type] ?? $this->mime_type;
    }
}
