<?php

namespace App\Services;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StorageService
{
    private string $disk;
    private string $bucket;

    public function __construct()
    {
        $this->disk = 's3';
        $this->bucket = config('filesystems.disks.s3.bucket');
    }

    public function uploadVideo(UploadedFile $file): array
    {
        try {
            $path = $this->generateUniqueVideoPath($file);

            $storedPath = Storage::disk($this->disk)->putFileAs(
                dirname($path),
                $file,
                basename($path),
                [
                    'visibility' => 'public',
                    'ContentType' => $file->getMimeType(),
                    'CacheControl' => 'max-age=31536000',
                ]
            );

            return [
                's3_path' => Storage::disk($this->disk)->url($storedPath),
                's3_key' => $storedPath,
            ];
        } catch (\Exception $e) {
            throw new Exception("Erro no upload: " . $e->getMessage());
        }
    }

    public function deleteVideo(string $s3Key): bool
    {
        try {
            return Storage::disk($this->disk)->delete($s3Key);
        } catch (\Exception $e) {
            throw new Exception("Erro ao deletar: " . $e->getMessage());
        }
    }

    public function getUrl(string $s3Key): string
    {
        return Storage::disk($this->disk)->url($s3Key);
    }

    public function generateSignedUrl(string $s3Key, int $expirationMinutes = 60): string
    {
        return Storage::disk($this->disk)->temporaryUrl(
            $s3Key,
            now()->addMinutes($expirationMinutes)
        );
    }

    private function generateUniqueVideoPath(UploadedFile $file): string
    {
        $uuid = Str::uuid();
        $timestamp = now()->format('Y/m/d/H-i-s');
        $extension = $file->getClientOriginalExtension();
        $sanitizedName = $this->sanitizeFilename(
            pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)
        );

        return "videos/{$timestamp}/{$uuid}_{$sanitizedName}.{$extension}";
    }

    private function sanitizeFilename(string $filename): string
    {
        $sanitized = preg_replace('/[^A-Za-z0-9_-]/', '_', $filename);
        return Str::limit($sanitized, 50, '');
    }
}
