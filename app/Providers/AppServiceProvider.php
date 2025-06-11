<?php

namespace App\Providers;

use App\Repositories\VideoRepository;
use App\Repositories\VideoRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(VideoRepositoryInterface::class, VideoRepository::class);
    }
}
