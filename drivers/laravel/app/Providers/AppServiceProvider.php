<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\ProcessPodcastEvent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use SendPodcastNotification;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(
            ProcessPodcastEvent::class,
            SendPodcastNotification::class,
        );
    }
}
