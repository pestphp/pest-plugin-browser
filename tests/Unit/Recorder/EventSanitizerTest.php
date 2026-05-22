<?php

declare(strict_types=1);

use Pest\Browser\Recorder\EventSanitizer;
use Pest\Browser\Recorder\RecordedEvent;

function makeRecordedEvent(string $type, ?array $locator = null, ?string $url = null, ?string $text = null): RecordedEvent
{
    return new RecordedEvent(
        type: $type,
        url: $url,
        locator: $locator,
        text: $text,
    );
}

function makeTestIdLocator(string $id): array
{
    return [
        'kind' => 'test-id',
        'body' => $id,
        'options' => [],
    ];
}

function makeRoleLocator(string $role, string $name): array
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
        makeRecordedEvent('navigate', url: 'http://localhost/'),
        makeRecordedEvent('click', makeTestIdLocator('btn')),
        makeRecordedEvent('fill', makeTestIdLocator('email')),
        makeRecordedEvent('check', makeTestIdLocator('remember')),
        makeRecordedEvent('uncheck', makeTestIdLocator('remember')),
        makeRecordedEvent('assertVisible', makeTestIdLocator('heading')),
        makeRecordedEvent('assertText', makeTestIdLocator('msg'), text: 'Hello'),
    ];

    expect($sanitizer->sanitize($events))->toHaveCount(7);
});

it('drops unsupported event types', function (): void {
    $sanitizer = new EventSanitizer('id');
    $events = [
        makeRecordedEvent('hover', makeTestIdLocator('btn')),
        makeRecordedEvent('press', makeTestIdLocator('input')),
        makeRecordedEvent('navigate', url: 'http://localhost/'),
    ];
    $result = $sanitizer->sanitize($events);

    expect($result)->toHaveCount(1)
        ->and($result[0]->type)->toBe('navigate');
});

it('drops navigate events without url', function (): void {
    $sanitizer = new EventSanitizer('id');
    $events = [
        makeRecordedEvent('navigate'),
        makeRecordedEvent('navigate', url: 'http://localhost/'),
    ];
    $result = $sanitizer->sanitize($events);

    expect($result)->toHaveCount(1);
});

it('drops events with null locator (except navigate)', function (): void {
    $sanitizer = new EventSanitizer('id');
    $events = [
        makeRecordedEvent('click'),
        makeRecordedEvent('fill'),
        makeRecordedEvent('navigate', url: 'http://localhost/'),
    ];
    $result = $sanitizer->sanitize($events);

    expect($result)->toHaveCount(1)
        ->and($result[0]->type)->toBe('navigate');
});

it('drops events where locator resolves to null selector', function (): void {
    $sanitizer = new EventSanitizer('id');
    $events = [
        makeRecordedEvent('click', [
            'kind' => 'unknown',
            'body' => '',
            'options' => [],
        ]),
        makeRecordedEvent('navigate', url: 'http://localhost/'),
    ];
    $result = $sanitizer->sanitize($events);

    expect($result)->toHaveCount(1);
});

// dropRedundantClicks
it('drops click when immediately followed by fill on same selector', function (): void {
    $sanitizer = new EventSanitizer('id');
    $events = [
        makeRecordedEvent('click', makeTestIdLocator('email')),
        makeRecordedEvent('fill', makeTestIdLocator('email'), text: 'user@example.com'),
    ];
    $result = $sanitizer->sanitize($events);

    expect($result)->toHaveCount(1)
        ->and($result[0]->type)->toBe('fill');
});

it('keeps click when followed by fill on different selector', function (): void {
    $sanitizer = new EventSanitizer('id');
    $events = [
        makeRecordedEvent('click', makeTestIdLocator('btn')),
        makeRecordedEvent('fill', makeTestIdLocator('email'), text: 'user@example.com'),
    ];
    $result = $sanitizer->sanitize($events);

    expect($result)->toHaveCount(2);
});

it('keeps click not followed by fill', function (): void {
    $sanitizer = new EventSanitizer('id');
    $events = [
        makeRecordedEvent('click', makeRoleLocator('button', 'Submit')),
        makeRecordedEvent('navigate', url: 'http://localhost/dashboard'),
    ];
    $result = $sanitizer->sanitize($events);

    expect($result)->toHaveCount(2);
});

// deduplicateFills
it('keeps only the last fill for each selector', function (): void {
    $sanitizer = new EventSanitizer('id');
    $events = [
        makeRecordedEvent('fill', makeTestIdLocator('email'), text: 'first@example.com'),
        makeRecordedEvent('fill', makeTestIdLocator('email'), text: 'second@example.com'),
        makeRecordedEvent('fill', makeTestIdLocator('email'), text: 'final@example.com'),
    ];
    $result = $sanitizer->sanitize($events);

    expect($result)->toHaveCount(1)
        ->and($result[0]->text)->toBe('final@example.com');
});

it('keeps fills for different selectors', function (): void {
    $sanitizer = new EventSanitizer('id');
    $events = [
        makeRecordedEvent('fill', makeTestIdLocator('email'), text: 'user@example.com'),
        makeRecordedEvent('fill', makeTestIdLocator('password'), text: 'secret'),
    ];
    $result = $sanitizer->sanitize($events);

    expect($result)->toHaveCount(2);
});

it('keeps assertText with unresolvable locator when text is non-empty', function (): void {
    $sanitizer = new EventSanitizer('id');
    $events = [
        makeRecordedEvent('assertText', ['kind' => 'role', 'body' => 'main', 'options' => ['attrs' => []]], text: 'Welcome'),
    ];
    $result = $sanitizer->sanitize($events);
    expect($result)->toHaveCount(1)
        ->and($result[0]->text)->toBe('Welcome');
});

it('keeps assertText with null locator when text is non-empty', function (): void {
    $sanitizer = new EventSanitizer('id');
    $events = [makeRecordedEvent('assertText', text: 'Welcome')];
    $result = $sanitizer->sanitize($events);
    expect($result)->toHaveCount(1);
});

it('drops assertText when text is empty', function (): void {
    $sanitizer = new EventSanitizer('id');
    $events = [makeRecordedEvent('assertText', text: '')];
    expect($sanitizer->sanitize($events))->toHaveCount(0);
});

it('keeps fills for same selector on different pages', function (): void {
    $sanitizer = new EventSanitizer('id');
    $events = [
        makeRecordedEvent('navigate', url: 'http://localhost/login'),
        makeRecordedEvent('fill', makeTestIdLocator('email'), text: 'admin@a.com'),
        makeRecordedEvent('navigate', url: 'http://localhost/register'),
        makeRecordedEvent('fill', makeTestIdLocator('email'), text: 'user@b.com'),
    ];
    $result = $sanitizer->sanitize($events);
    expect($result)->toHaveCount(4)
        ->and($result[1]->text)->toBe('admin@a.com')
        ->and($result[3]->text)->toBe('user@b.com');
});

it('preserves event order after deduplication', function (): void {
    $sanitizer = new EventSanitizer('id');
    $events = [
        makeRecordedEvent('fill', makeTestIdLocator('email'), text: 'first@example.com'),
        makeRecordedEvent('fill', makeTestIdLocator('password'), text: 'secret'),
        makeRecordedEvent('fill', makeTestIdLocator('email'), text: 'final@example.com'),
    ];
    $result = $sanitizer->sanitize($events);

    expect($result)->toHaveCount(2)
        ->and($result[0]->text)->toBe('secret')
        ->and($result[1]->text)->toBe('final@example.com');
});
