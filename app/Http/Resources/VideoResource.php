<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VideoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'original_filename' => $this->original_filename,
            'url' => $this->s3_path,
            'mime_type' => $this->mime_type,
            'file_size' => $this->file_size,
            'file_size_human' => $this->formatFileSize($this->file_size),
            'resolution' => $this->resolution,
            'duration_seconds' => $this->duration_seconds,
            'duration_formatted' => $this->duration_formatted,
            'status' => $this->status,
            'metadata' => $this->metadata,
            'uploaded_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    private function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
