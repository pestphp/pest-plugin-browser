<?php

use App\Events\PodcastProcessedEvent;

class SendPodcastNotification
{
    /**
     * Handle the event.
     */
    public function handle(PodcastProcessedEvent $event): void
    {
        // ...
    }
}
