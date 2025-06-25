<?php

namespace App\Listeners;

use App\Events\ProcessPodcastEvent;
use Illuminate\Support\Facades\Notification;
use Illuminate\Notifications\Notification as BaseNotification;

class DummyNotification extends BaseNotification {
    public function via(
        $notifiable
    ) {
        return ['database'];
    }
    public function toArray($notifiable) {
        return ['message' => 'Podcast processed!'];
    }
}

class SendPodcastNotification
{
    public static ?ProcessPodcastEvent $lastEvent = null;
    public static array $sentNotifications = [];

    /**
     * Handle the event.
     */
    public function handle(ProcessPodcastEvent $event): void
    {
        self::$lastEvent = $event;
        // Simulate sending a notification to a notifiable entity (e.g., user with ID 1)
        $notifiable = (object) ['id' => 1, 'routeNotificationFor' => fn($driver) => 'user-1'];
        Notification::send($notifiable, new DummyNotification());
        self::$sentNotifications[] = $notifiable;
    }
}
