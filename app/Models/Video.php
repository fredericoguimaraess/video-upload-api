<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasFactory;

    protected $fillable = [
        'original_filename',
        's3_path',
        's3_key',
        'mime_type',
        'file_size',
        'resolution',
        'duration_seconds',
        'duration_formatted',
        'status',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'file_size' => 'integer',
        'duration_seconds' => 'integer',
    ];

    public function getUrlAttribute(): string
    {
        return config('filesystems.disks.s3.url') . '/' . $this->s3_key;
    }

    public function isProcessed(): bool
    {
        return $this->status === 'completed';
    }
}
