<?php

declare(strict_types=1);

use App\Events\ProcessPodcastEvent;
use App\Listeners\SendPodcastNotification;
use Illuminate\Support\Facades\Event;

it('may fake events', function (): void {
    Event::fake();

    $page = page(route('event'));

    $content = json_decode((string) $page->textContent(), true);

    expect($content)->toBeArray()
        ->and($content['message'])->toBe('Event dispatched successfully.')
        ->and($content['status'])->toBe('success');

    Event::assertDispatched(ProcessPodcastEvent::class);
});

it('listener handles the event', function (): void {
    // Reset the static property before the test
    SendPodcastNotification::$lastEvent = null;

    Event::fake();

    $page = page(route('event'));

    expect(SendPodcastNotification::$lastEvent)
        ->toBeInstanceOf(ProcessPodcastEvent::class);
});
