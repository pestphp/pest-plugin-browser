<?php

declare(strict_types=1);

use Pest\Browser\Playwright\JSHandle;

test('constructs JSHandle with guid correctly', function (): void {
    $guid = 'test-guid-12345';
    $handle = new JSHandle($guid);

    expect($handle->guid())->toBe($guid);
});

test('has correct class properties', function (): void {
    $guid = 'test-guid';
    $handle = new JSHandle($guid);

    expect($handle)->toBeInstanceOf(JSHandle::class);
    expect(property_exists($handle, 'guid'))->toBeTrue();
});

test('can be created with different guid formats', function (): void {
    $guidFormats = [
        'simple-guid',
        'uuid-v4-format-12345',
        'handle-with-numbers-123',
        'CamelCaseGuid',
        'guid_with_underscores',
    ];

    foreach ($guidFormats as $guid) {
        $handle = new JSHandle($guid);
        expect($handle->guid())->toBe($guid);
        expect($handle)->toBeInstanceOf(JSHandle::class);
    }
});

test('has unique guid for different instances', function (): void {
    $handle1 = new JSHandle('guid-1');
    $handle2 = new JSHandle('guid-2');

    expect($handle1->guid())->not()->toBe($handle2->guid());
    expect($handle1)->not()->toBe($handle2);
});
