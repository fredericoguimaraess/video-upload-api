<?php

namespace App\Services;

use App\Events\VideoUploaded;
use App\Exceptions\VideoProcessingException;
use App\Models\Video;
use App\Repositories\VideoRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VideoService
{
    public function __construct(
        private VideoRepositoryInterface $videoRepository,
        private S3Service $s3Service,
        private VideoProcessorService $videoProcessor
    ) {}

    public function uploadVideo(UploadedFile $file): Video
    {
        DB::beginTransaction();

        try {
            $s3Data = $this->s3Service->uploadVideo($file);

            $video = $this->videoRepository->create([
                'original_filename' => $file->getClientOriginalName(),
                's3_path' => $s3Data['s3_path'],
                's3_key' => $s3Data['s3_key'],
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'status' => 'processing'
            ]);

            $this->processVideoMetadata($video, $file->getRealPath());

            DB::commit();

            event(new VideoUploaded($video));

            return $video;

        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($s3Data['s3_key'])) {
                $this->s3Service->deleteVideo($s3Data['s3_key']);
            }

            Log::error('Erro no upload de vídeo', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName()
            ]);

            throw new VideoProcessingException('Erro no upload do vídeo: ' . $e->getMessage());
        }
    }

    private function processVideoMetadata(Video $video, string $tempPath): void
    {
        try {
            $metadata = $this->videoProcessor->extractMetadata($tempPath);

            $this->videoRepository->update($video, [
                'resolution' => $metadata['resolution'],
                'duration_seconds' => $metadata['duration_seconds'],
                'duration_formatted' => $metadata['duration_formatted'],
                'metadata' => $metadata['metadata'],
                'status' => 'completed'
            ]);

        } catch (VideoProcessingException $e) {
            $this->videoRepository->update($video, [
                'status' => 'failed'
            ]);

            Log::error('Erro no processamento de metadados', [
                'video_id' => $video->id,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    public function getVideo(int $id): ?Video
    {
        return $this->videoRepository->findById($id);
    }

    public function getAllVideos(int $perPage = 15)
    {
        return $this->videoRepository->getAllPaginated($perPage);
    }
}
