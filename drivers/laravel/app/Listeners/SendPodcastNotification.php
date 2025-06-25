<?php

namespace App\Listeners;

use App\Events\ProcessPodcastEvent;

class SendPodcastNotification
{
    public static ?ProcessPodcastEvent $lastEvent = null;

    /**
     * Handle the event.
     */
    public function handle(ProcessPodcastEvent $event): void
    {
        self::$lastEvent = $event;
        // ...
    }
}
