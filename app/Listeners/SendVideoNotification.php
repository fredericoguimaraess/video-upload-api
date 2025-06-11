<?php

namespace App\Listeners;

use App\Events\VideoUploaded;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class SendVideoNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(VideoUploaded $event): void
    {
        $video = $event->video;

        $message = [
            'event' => 'video_uploaded',
            'video_id' => $video->id,
            's3_path' => $video->s3_path,
            's3_key' => $video->s3_key,
            'original_filename' => $video->original_filename,
            'status' => $video->status,
            'timestamp' => now()->toISOString(),
        ];

        Queue::push('video-notifications', json_encode($message));

        Log::info('Notificação de vídeo enviada', [
            'video_id' => $video->id,
            'message' => $message
        ]);
    }
}
