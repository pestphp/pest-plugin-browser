<?php

declare(strict_types=1);

use Pest\Browser\Support\StorageState;

it('places storage state files under tests/Browser/StorageState', function (): void {
    StorageState::save('{"cookies":[],"origins":[]}', 'auth');

    expect(file_exists(StorageState::path('auth')))->toBeTrue();

    @unlink(StorageState::path('auth'));
});

it('returns the name used to save the state', function (): void {
    $name = StorageState::save('{"cookies":[],"origins":[]}', 'my-session');

    expect($name)->toBe('my-session');

    @unlink(StorageState::path('my-session'));
});

it('builds the correct file path from a name', function (): void {
    $path = StorageState::path('auth');

    expect($path)->toEndWith('tests/Browser/StorageState/auth.json');
});

it('strips a leading slash from the name', function (): void {
    $path = StorageState::path('/auth');

    expect($path)->not->toContain('//');
    expect($path)->toEndWith('tests/Browser/StorageState/auth.json');
});

it('overwrites an existing state file on re-save', function (): void {
    StorageState::save('{"cookies":[],"origins":[]}', 'overwrite-test');
    StorageState::save('{"cookies":[{"name":"session"}],"origins":[]}', 'overwrite-test');

    $contents = file_get_contents(StorageState::path('overwrite-test'));

    expect($contents)->toContain('session');

    @unlink(StorageState::path('overwrite-test'));
});
