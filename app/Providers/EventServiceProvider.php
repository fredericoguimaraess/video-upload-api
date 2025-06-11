<?php

namespace App\Providers;

use App\Events\VideoUploaded;
use App\Listeners\SendVideoNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        VideoUploaded::class => [
            SendVideoNotification::class,
        ],
    ];
}
