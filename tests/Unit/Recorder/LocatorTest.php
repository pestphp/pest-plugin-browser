<?php

declare(strict_types=1);

use Pest\Browser\Recorder\Locator;

// test-id kind
it('resolves test-id with id attribute to hash selector', function (): void {
    $locator = Locator::fromArray([
        'kind' => 'test-id',
        'body' => 'submit-btn',
        'options' => [],
    ]);

    expect($locator->toSelector('id'))->toBe('#submit-btn');
});

it('resolves test-id with data-test attribute to @ selector', function (): void {
    $locator = Locator::fromArray([
        'kind' => 'test-id',
        'body' => 'submit-btn',
        'options' => [],
    ]);

    expect($locator->toSelector('data-test'))->toBe('@submit-btn');
});

it('resolves test-id with data-testid attribute to @ selector', function (): void {
    $locator = Locator::fromArray([
        'kind' => 'test-id',
        'body' => 'submit-btn',
        'options' => [],
    ]);

    expect($locator->toSelector('data-testid'))->toBe('@submit-btn');
});

it('resolves test-id with custom attribute to attribute selector', function (): void {
    $locator = Locator::fromArray([
        'kind' => 'test-id',
        'body' => 'submit-btn',
        'options' => [],
    ]);

    expect($locator->toSelector('data-cy'))->toBe('[data-cy="submit-btn"]');
});

it('returns null for test-id with empty body', function (): void {
    $locator = Locator::fromArray([
        'kind' => 'test-id',
        'body' => '',
        'options' => [],
    ]);

    expect($locator->toSelector('id'))->toBeNull();
});

// role kind — clickable elements use name directly
it('resolves role=button to name', function (): void {
    $locator = Locator::fromArray([
        'kind' => 'role',
        'body' => 'button',
        'options' => ['name' => 'Submit'],
    ]);

    expect($locator->toSelector('id'))->toBe('Submit');
});

it('resolves role=link to name', function (): void {
    $locator = Locator::fromArray([
        'kind' => 'role',
        'body' => 'link',
        'options' => ['name' => 'Dashboard'],
    ]);

    expect($locator->toSelector('id'))->toBe('Dashboard');
});

it('resolves role=menuitem to name', function (): void {
    $locator = Locator::fromArray([
        'kind' => 'role',
        'body' => 'menuitem',
        'options' => ['name' => 'Settings'],
    ]);

    expect($locator->toSelector('id'))->toBe('Settings');
});

it('resolves role=checkbox to name', function (): void {
    $locator = Locator::fromArray([
        'kind' => 'role',
        'body' => 'checkbox',
        'options' => ['name' => 'Remember me'],
    ]);

    expect($locator->toSelector('id'))->toBe('Remember me');
});

// role kind — input elements use label selector
it('resolves role=textbox to label selector', function (): void {
    $locator = Locator::fromArray([
        'kind' => 'role',
        'body' => 'textbox',
        'options' => ['name' => 'Email address'],
    ]);

    expect($locator->toSelector('id'))->toBe('internal:label="Email address"s');
});

it('resolves role=searchbox to label selector', function (): void {
    $locator = Locator::fromArray([
        'kind' => 'role',
        'body' => 'searchbox',
        'options' => ['name' => 'Search'],
    ]);

    expect($locator->toSelector('id'))->toBe('internal:label="Search"s');
});

it('resolves role=combobox to label selector', function (): void {
    $locator = Locator::fromArray([
        'kind' => 'role',
        'body' => 'combobox',
        'options' => ['name' => 'Country'],
    ]);

    expect($locator->toSelector('id'))->toBe('internal:label="Country"s');
});

it('returns null for role with empty name', function (): void {
    $locator = Locator::fromArray([
        'kind' => 'role',
        'body' => 'button',
        'options' => ['name' => ''],
    ]);

    expect($locator->toSelector('id'))->toBeNull();
});

it('returns null for unhandled role body', function (): void {
    $locator = Locator::fromArray([
        'kind' => 'role',
        'body' => 'grid',
        'options' => ['name' => 'Data'],
    ]);

    expect($locator->toSelector('id'))->toBeNull();
});

// text / css / default kinds
it('resolves text kind to body', function (): void {
    $locator = Locator::fromArray([
        'kind' => 'text',
        'body' => 'Click here',
        'options' => [],
    ]);

    expect($locator->toSelector('id'))->toBe('Click here');
});

it('resolves css kind to body', function (): void {
    $locator = Locator::fromArray([
        'kind' => 'css',
        'body' => '.btn-primary',
        'options' => [],
    ]);

    expect($locator->toSelector('id'))->toBe('.btn-primary');
});

it('resolves default kind to body', function (): void {
    $locator = Locator::fromArray([
        'kind' => 'default',
        'body' => 'input[type=email]',
        'options' => [],
    ]);

    expect($locator->toSelector('id'))->toBe('input[type=email]');
});

it('returns null for unknown kind', function (): void {
    $locator = Locator::fromArray([
        'kind' => 'label',
        'body' => 'Email',
        'options' => [],
    ]);

    expect($locator->toSelector('id'))->toBeNull();
});

it('uses defaults when array keys are missing', function (): void {
    $locator = Locator::fromArray([]);

    expect($locator->toSelector('id'))->toBeNull();
});
