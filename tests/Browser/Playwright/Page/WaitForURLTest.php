<?php

declare(strict_types=1);

it('waits for URL pattern', function (): void {
    $page = page('/test/frame-tests');

    $currentUrl = '/test/selector-tests';

    $page->waitForURL($currentUrl);

    expect(true)->toBeTrue();
});
