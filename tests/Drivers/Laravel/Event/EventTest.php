<?php

declare(strict_types=1);

use App\Events\ProcessPodcastEvent;
use App\Listeners\SendPodcastNotification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

it('may fake events', function (): void {
    Event::fake();

    $page = page(route('event'));

    $content = json_decode((string) $page->textContent(), true);

    expect($content)->toBeArray()
        ->and($content['message'])->toBe('Event dispatched successfully.')
        ->and($content['status'])->toBe('success');

    Event::assertDispatched(ProcessPodcastEvent::class);
});

it('listener handles the event and sends notification', function (): void {
    // Reset the static properties before the test
    SendPodcastNotification::$lastEvent = null;
    SendPodcastNotification::$sentNotifications = [];

    // Fake events and notifications
    Event::fake();
    Notification::fake();

    // Dispatch the event directly
    event(new ProcessPodcastEvent());

    // Manually call the listener since Event::fake() prevents listeners from running
    (new SendPodcastNotification())->handle(new ProcessPodcastEvent());

    expect(SendPodcastNotification::$lastEvent)
        ->toBeInstanceOf(ProcessPodcastEvent::class);

    expect(SendPodcastNotification::$sentNotifications)
        ->not->toBeEmpty()
        ->and(SendPodcastNotification::$sentNotifications[0]->id)->toBe(1);
});
