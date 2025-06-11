<?php

namespace Tests\Unit;

use App\Models\Video;
use App\Repositories\VideoRepositoryInterface;
use App\Services\S3Service;
use App\Services\VideoProcessorService;
use App\Services\VideoService;
use Illuminate\Http\UploadedFile;
use Mockery;
use Tests\TestCase;

class VideoServiceTest extends TestCase
{
    public function test_upload_video_creates_video_record(): void
    {
        $mockRepository = Mockery::mock(VideoRepositoryInterface::class);
        $mockS3Service = Mockery::mock(S3Service::class);
        $mockProcessor = Mockery::mock(VideoProcessorService::class);

        $file = UploadedFile::fake()->create('test.mp4', 1000, 'video/mp4');

        $mockS3Service->shouldReceive('uploadVideo')
                     ->once()
                     ->andReturn([
                         's3_path' => 'https://bucket.s3.amazonaws.com/videos/test.mp4',
                         's3_key' => 'videos/test.mp4'
                     ]);

        $video = new Video([
            'id' => 1,
            'original_filename' => 'test.mp4',
            'status' => 'processing'
        ]);

        $mockRepository->shouldReceive('create')
                      ->once()
                      ->andReturn($video);

        $mockProcessor->shouldReceive('extractMetadata')
                     ->once()
                     ->andReturn([
                         'resolution' => '1920x1080',
                         'duration_seconds' => 120,
                         'duration_formatted' => '00:02:00',
                         'metadata' => []
                     ]);

        $mockRepository->shouldReceive('update')
                      ->once()
                      ->andReturn($video);

        $service = new VideoService($mockRepository, $mockS3Service, $mockProcessor);
        $result = $service->uploadVideo($file);

        $this->assertInstanceOf(Video::class, $result);
    }
}
