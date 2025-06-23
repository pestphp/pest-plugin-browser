<?php

declare(strict_types=1);

use App\Jobs\ProcessPodcast;
use Illuminate\Support\Facades\Queue;

it('may fake queue jobs', function (): void {
    Queue::fake();

    $page = page(route('queue'));

    $content = json_decode((string) $page->textContent(), true);

    expect($content)->toBeArray()
        ->and($content['message'])->toBe('Queue job dispatched successfully.')
        ->and($content['status'])->toBe('success');

    Queue::assertPushed(ProcessPodcast::class);
});
