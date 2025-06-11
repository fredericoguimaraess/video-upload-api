<?php

namespace App\Services;

use App\Exceptions\VideoProcessingException;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;

class VideoProcessorService
{
    private FFMpeg $ffmpeg;
    private FFProbe $ffprobe;

    public function __construct()
    {
        try {
            $this->ffmpeg = FFMpeg::create();
            $this->ffprobe = FFProbe::create();
        } catch (\Exception $e) {
            throw new VideoProcessingException("FFmpeg não está disponível: " . $e->getMessage());
        }
    }

    public function extractMetadata(string $videoPath): array
    {
        try {
            $ffprobe = $this->ffprobe;

            $duration = $ffprobe->format($videoPath)->get('duration');
            $durationSeconds = (int) $duration;
            $durationFormatted = $this->formatDuration($durationSeconds);

            $videoStream = $ffprobe->streams($videoPath)->videos()->first();
            $width = $videoStream->get('width');
            $height = $videoStream->get('height');
            $resolution = "{$width}x{$height}";

            return [
                'duration_seconds' => $durationSeconds,
                'duration_formatted' => $durationFormatted,
                'resolution' => $resolution,
                'metadata' => [
                    'width' => $width,
                    'height' => $height,
                    'duration' => $duration,
                    'bit_rate' => $ffprobe->format($videoPath)->get('bit_rate'),
                    'codec' => $videoStream->get('codec_name'),
                ]
            ];
        } catch (\Exception $e) {
            throw new VideoProcessingException("Erro ao extrair metadados: " . $e->getMessage());
        }
    }

    private function formatDuration(int $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $remainingSeconds = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $remainingSeconds);
    }
}
