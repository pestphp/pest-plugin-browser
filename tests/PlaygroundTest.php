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

    $page = visit(route('dashboard'));

    $page->assertSee('Taylor Otwell');

    Queue::assertPushed(ProcessPodcast::class);
});
