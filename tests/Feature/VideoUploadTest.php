<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VideoUploadTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        Storage::fake('s3');
    }

    public function test_can_upload_video_successfully(): void
    {
        $video = UploadedFile::fake()->create('test_video.mp4', 5000, 'video/mp4');

        $response = $this->postJson('/api/videos', [
            'video' => $video
        ]);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'id',
                        'original_filename',
                        'url',
                        'status'
                    ]
                ]);

        $this->assertDatabaseHas('videos', [
            'original_filename' => 'test_video.mp4',
            'mime_type' => 'video/mp4'
        ]);
    }

    public function test_video_upload_requires_file(): void
    {
        $response = $this->postJson('/api/videos', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['video']);
    }

    public function test_video_upload_validates_file_type(): void
    {
        $invalidFile = UploadedFile::fake()->create('document.pdf', 1000, 'application/pdf');

        $response = $this->postJson('/api/videos', [
            'video' => $invalidFile
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['video']);
    }

    public function test_video_upload_validates_file_size(): void
    {
        $largeFile = UploadedFile::fake()->create('large_video.mp4', 150000, 'video/mp4'); // 150MB

        $response = $this->postJson('/api/videos', [
            'video' => $largeFile
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['video']);
    }
}
