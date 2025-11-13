<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OdpDetection extends Model
{
    use HasFactory;

    protected $table = 'odp_detections';

    protected $fillable = [
        'photo_id',
        'hasil_deteksi',
        'klasifikasi_fisik',
        'label_odp',
        'status',
        'bbox',
        'detector_conf',
        'classifier_conf',
        'ocr_text',
        'ocr_conf', // ✅ sesuai migrasi
        'reviewed',
    ];

    protected $casts = [
        'bbox' => 'array',
        'reviewed' => 'boolean',
        'detector_conf' => 'float',
        'classifier_conf' => 'float',
        'ocr_conf' => 'float', // ✅ sesuai migrasi
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function photo()
    {
        return $this->belongsTo(Photo::class, 'photo_id', 'photo_id');
    }
}
