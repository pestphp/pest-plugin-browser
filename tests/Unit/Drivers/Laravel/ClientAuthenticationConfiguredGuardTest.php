<?php

declare(strict_types=1);

use Illuminate\Auth\AuthManager;
use Pest\Browser\Drivers\ClientAuthentication;

it('gives a request of the frontend the empty default guard the application configured', function (): void {
    config(['auth.defaults.guard' => null]);

    ClientAuthentication::rememberConfiguredGuard();

    config(['auth.defaults.guard' => 'admin']);

    $auth = app('auth');
    assert($auth instanceof AuthManager);

    $authentication = new ClientAuthentication($auth, null);
    $authentication->isolate();

    expect(config('auth.defaults.guard'))->toBeNull();

    $authentication->restore();

    expect(config('auth.defaults.guard'))->toBe('admin');
});
