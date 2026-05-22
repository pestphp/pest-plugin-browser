<?php

declare(strict_types=1);

use Pest\Browser\Recorder\EventSanitizer;
use Pest\Browser\Recorder\RecordedEvent;

function makeEvent(string $type, ?array $locator = null, ?string $url = null, ?string $text = null): RecordedEvent
{
    return new RecordedEvent(
        type: $type,
        url: $url,
        locator: $locator,
        text: $text,
    );
}

function testIdLocator(string $id): array
{
    return [
        'kind' => 'test-id',
        'body' => $id,
        'options' => [],
    ];
}

function roleLocator(string $role, string $name): array
{
    return [
        'kind' => 'role',
        'body' => $role,
        'options' => [
            'name' => $name,
        ],
    ];
}

// dropUnsupported
it('keeps supported event types', function (): void {
    $sanitizer = new EventSanitizer('id');
    $events = [
        makeEvent('navigate', url: 'http://localhost/'),
        makeEvent('click', testIdLocator('btn')),
        makeEvent('fill', testIdLocator('email')),
        makeEvent('check', testIdLocator('remember')),
        makeEvent('uncheck', testIdLocator('remember')),
        makeEvent('assertVisible', testIdLocator('heading')),
        makeEvent('assertText', testIdLocator('msg'), text: 'Hello'),
    ];

    expect($sanitizer->sanitize($events))->toHaveCount(7);
});

it('drops unsupported event types', function (): void {
    $sanitizer = new EventSanitizer('id');
    $events = [
        makeEvent('hover', testIdLocator('btn')),
        makeEvent('press', testIdLocator('input')),
        makeEvent('navigate', url: 'http://localhost/'),
    ];
    $result = $sanitizer->sanitize($events);

    expect($result)->toHaveCount(1)
        ->and($result[0]->type)->toBe('navigate');
});

it('drops navigate events without url', function (): void {
    $sanitizer = new EventSanitizer('id');
    $events = [
        makeEvent('navigate'),
        makeEvent('navigate', url: 'http://localhost/'),
    ];
    $result = $sanitizer->sanitize($events);

    expect($result)->toHaveCount(1);
});

it('drops events with null locator (except navigate)', function (): void {
    $sanitizer = new EventSanitizer('id');
    $events = [
        makeEvent('click'),
        makeEvent('fill'),
        makeEvent('navigate', url: 'http://localhost/'),
    ];
    $result = $sanitizer->sanitize($events);

    expect($result)->toHaveCount(1)
        ->and($result[0]->type)->toBe('navigate');
});

it('drops events where locator resolves to null selector', function (): void {
    $sanitizer = new EventSanitizer('id');
    $events = [
        makeEvent('click', [
            'kind' => 'unknown',
            'body' => '',
            'options' => [],
        ]),
        makeEvent('navigate', url: 'http://localhost/'),
    ];
    $result = $sanitizer->sanitize($events);

    expect($result)->toHaveCount(1);
});

// dropRedundantClicks
it('drops click when immediately followed by fill on same selector', function (): void {
    $sanitizer = new EventSanitizer('id');
    $events = [
        makeEvent('click', testIdLocator('email')),
        makeEvent('fill', testIdLocator('email'), text: 'user@example.com'),
    ];
    $result = $sanitizer->sanitize($events);

    expect($result)->toHaveCount(1)
        ->and($result[0]->type)->toBe('fill');
});

it('keeps click when followed by fill on different selector', function (): void {
    $sanitizer = new EventSanitizer('id');
    $events = [
        makeEvent('click', testIdLocator('btn')),
        makeEvent('fill', testIdLocator('email'), text: 'user@example.com'),
    ];
    $result = $sanitizer->sanitize($events);

    expect($result)->toHaveCount(2);
});

it('keeps click not followed by fill', function (): void {
    $sanitizer = new EventSanitizer('id');
    $events = [
        makeEvent('click', roleLocator('button', 'Submit')),
        makeEvent('navigate', url: 'http://localhost/dashboard'),
    ];
    $result = $sanitizer->sanitize($events);

    expect($result)->toHaveCount(2);
});

// deduplicateFills
it('keeps only the last fill for each selector', function (): void {
    $sanitizer = new EventSanitizer('id');
    $events = [
        makeEvent('fill', testIdLocator('email'), text: 'first@example.com'),
        makeEvent('fill', testIdLocator('email'), text: 'second@example.com'),
        makeEvent('fill', testIdLocator('email'), text: 'final@example.com'),
    ];
    $result = $sanitizer->sanitize($events);

    expect($result)->toHaveCount(1)
        ->and($result[0]->text)->toBe('final@example.com');
});

it('keeps fills for different selectors', function (): void {
    $sanitizer = new EventSanitizer('id');
    $events = [
        makeEvent('fill', testIdLocator('email'), text: 'user@example.com'),
        makeEvent('fill', testIdLocator('password'), text: 'secret'),
    ];
    $result = $sanitizer->sanitize($events);

    expect($result)->toHaveCount(2);
});

it('preserves event order after deduplication', function (): void {
    $sanitizer = new EventSanitizer('id');
    $events = [
        makeEvent('fill', testIdLocator('email'), text: 'first@example.com'),
        makeEvent('fill', testIdLocator('password'), text: 'secret'),
        makeEvent('fill', testIdLocator('email'), text: 'final@example.com'),
    ];
    $result = $sanitizer->sanitize($events);

    expect($result)->toHaveCount(2)
        ->and($result[0]->text)->toBe('secret')
        ->and($result[1]->text)->toBe('final@example.com');
});
