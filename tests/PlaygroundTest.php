<?php

declare(strict_types=1);

use App\Jobs\ProcessPodcast;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

pest()->uses(RefreshDatabase::class);

test('example', function (): void {
    Queue::fake();

    $user = User::factory()->create([
        'name' => 'Taylor Otwell',
    ]);

    $this->actingAs($user);

    $page = page(route('dashboard'));

    $content = $page->locator('div');

    expect($content)->toHaveText('Hi Taylor Otwell!');

    Queue::assertPushed(ProcessPodcast::class);
});
