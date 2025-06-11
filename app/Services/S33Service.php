<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class S3Service
{
    private string $disk;

    public function __construct()
    {
        $this->disk = 's3';
    }

    public function uploadVideo(UploadedFile $file): array
    {
        $uuid = Str::uuid();
        $timestamp = now()->format('Y-m-d_H-i-s');
        $extension = $file->getClientOriginalExtension();
        $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        $s3Key = "videos/{$uuid}/{$filename}_{$timestamp}.{$extension}";

        $path = Storage::disk($this->disk)->putFileAs(
            dirname($s3Key),
            $file,
            basename($s3Key),
            'public'
        );

        return [
            's3_path' => Storage::disk($this->disk)->url($path),
            's3_key' => $path,
        ];
    }

    public function deleteVideo(string $s3Key): bool
    {
        return Storage::disk($this->disk)->delete($s3Key);
    }

    public function getUrl(string $s3Key): string
    {
        return Storage::disk($this->disk)->url($s3Key);
    }
}
