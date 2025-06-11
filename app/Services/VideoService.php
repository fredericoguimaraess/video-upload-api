<?php

namespace App\Services;

use App\Events\VideoUploaded;
use App\Exceptions\VideoProcessingException;
use App\Jobs\ProcessVideoJob;
use App\Models\Video;
use App\Repositories\VideoRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VideoService
{
    public function __construct(
        private VideoRepositoryInterface $videoRepository,
        private StorageService $storageService,
        private VideoProcessorService $videoProcessor
    ) {}

    public function uploadVideo(UploadedFile $file): Video
    {
        DB::beginTransaction();

        try {
            $this->validateVideoFile($file);

            $storageData = $this->storageService->uploadVideo($file);

            $video = $this->videoRepository->create([
                'original_filename' => $file->getClientOriginalName(),
                's3_path' => $storageData['s3_path'],
                's3_key' => $storageData['s3_key'],
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'status' => 'processing'
            ]);

            ProcessVideoJob::dispatch($video, $file->getRealPath())
                ->onQueue('video-processing');

            DB::commit();

            event(new VideoUploaded($video));

            Log::info('Vídeo enviado com sucesso', [
                'video_id' => $video->id,
                'filename' => $file->getClientOriginalName(),
                'size' => $file->getSize()
            ]);

            return $video;

        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($storageData['s3_key'])) {
                $this->storageService->deleteVideo($storageData['s3_key']);
            }

            Log::error('Erro no upload de vídeo', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new VideoProcessingException('Erro no upload: ' . $e->getMessage());
        }
    }

    private function validateVideoFile(UploadedFile $file): void
    {
        $allowedMimes = ['video/mp4', 'video/quicktime', 'video/x-msvideo'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file->getRealPath());
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedMimes)) {
            throw new VideoProcessingException('Tipo de arquivo não permitido: ' . $mimeType);
        }

        $maxSize = config('video.max_size', 104857600); // 100MB
        if ($file->getSize() > $maxSize) {
            throw new VideoProcessingException('Arquivo muito grande. Máximo: ' . ($maxSize / 1024 / 1024) . 'MB');
        }
    }

    public function getVideo(int $id): ?Video
    {
        return $this->videoRepository->findById($id);
    }

    public function getAllVideos(array $filters = [], int $perPage = 15)
    {
        return $this->videoRepository->getAllPaginated($filters, $perPage);
    }

    public function deleteVideo(int $id): bool
    {
        $video = $this->getVideo($id);

        if (!$video) {
            return false;
        }

        DB::beginTransaction();

        try {
            $this->storageService->deleteVideo($video->s3_key);

            $video->delete();

            DB::commit();

            Log::info('Vídeo deletado', ['video_id' => $id]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro ao deletar vídeo', [
                'video_id' => $id,
                'error' => $e->getMessage()
            ]);

            throw new VideoProcessingException('Erro ao deletar vídeo');
        }
    }
}
