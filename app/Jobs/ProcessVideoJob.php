<?php

namespace App\Jobs;

use App\Models\Video;
use App\Services\VideoProcessorService;
use App\Repositories\VideoRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessVideoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private Video $video,
        private string $tempPath
    ) {}

    public function handle(
        VideoProcessorService $processor,
        VideoRepositoryInterface $repository
    ): void {
        try {
            $metadata = $processor->extractMetadata($this->tempPath);

            $repository->update($this->video, [
                'resolution' => $metadata['resolution'],
                'duration_seconds' => $metadata['duration_seconds'],
                'duration_formatted' => $metadata['duration_formatted'],
                'metadata' => $metadata['metadata'],
                'status' => 'completed'
            ]);

            Log::info('Vídeo processado com sucesso', [
                'video_id' => $this->video->id
            ]);

        } catch (\Exception $e) {
            $repository->update($this->video, [
                'status' => 'failed'
            ]);

            Log::error('Erro no processamento do vídeo', [
                'video_id' => $this->video->id,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }
}
