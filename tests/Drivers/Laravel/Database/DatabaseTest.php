<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

it('shares the same database', function (): void {
    $page = page()->goto(route('database'));

    [
        'users' => $users,
        'database' => [
            'driver' => $driver,
            'name' => $name,
        ]
    ] = json_decode((string) $page->textContent(), true);

    expect($users)->toBeArray()
        ->and($users)->toHaveCount(0)
        ->and($driver)->toBe(config('database.default'))
        ->and($name)->toBe(config('database.connections.'.config('database.default').'.database'));

    User::factory()->create(['name' => 'Test User']);

    $page->goto(route('database'));

    ['users' => $users] = json_decode((string) $page->textContent(), true);

    expect($users)->toBeArray()
        ->and($users)->toHaveCount(1)
        ->and($users)->toBe(User::all()->toArray());
});
