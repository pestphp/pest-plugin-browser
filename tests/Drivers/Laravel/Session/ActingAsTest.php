<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->uses(RefreshDatabase::class);

it('may act as a user', function (): void {
    $user = User::factory()->create([
        'name' => 'Nuno Maduro',
    ]);

    $this->actingAs($user);

    $page = page()->goto(route('auth'));

    $content = json_decode((string) $page->textContent(), true);

    expect($content)->toBeArray()
        ->and($content['user_id'])->toBe($user->id)
        ->and($content['user_name'])->toBe($user->name)
        ->and($content['message'])->toBe('Hello, '.$user->name.'!');

    $user = User::factory()->create([
        'name' => 'Punyapal Shah',
    ]);

    $this->actingAs($user);

    $page = page()->goto(route('auth'));

    $content = json_decode((string) $page->textContent(), true);

    expect($content)->toBeArray()
        ->and($content['user_id'])->toBe($user->id)
        ->and($content['user_name'])->toBe($user->name)
        ->and($content['message'])->toBe('Hello, '.$user->name.'!');
});

it('may act as guest', function (): void {
    $page = page()->goto(route('auth'));

    $content = json_decode((string) $page->textContent(), true);

    expect($content)->toBeArray()
        ->and($content['user_id'])->toBeNull()
        ->and($content['user_name'])->toBe('Guest')
        ->and($content['message'])->toBe('Hello, Guest!');
});

it('may logout', function (): void {

    $user = User::factory()->create([
        'name' => 'Nuno Maduro',
    ]);

    $this->actingAs($user);

    $page = page()->goto(route('logout'));

    $content = json_decode((string) $page->textContent(), true);

    expect($content)->toBeArray()
        ->and($content['user_id'])->toBeNull()
        ->and($content['user_name'])->toBe('Guest')
        ->and($content['message'])->toBe('Hello, Guest!');
});
