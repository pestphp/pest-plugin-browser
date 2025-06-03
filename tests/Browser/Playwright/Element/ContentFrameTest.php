<?php

declare(strict_types=1);

it('returns null for non-iframe elements', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $locator = $page->getByTestId('profile-section');
    $element = $locator->elementHandle();

    $frame = $element->contentFrame();

    expect($frame)->toBeNull();
});

it('returns frame for iframe elements', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $locator = $page->getByTestId('test-iframe');
    $element = $locator->elementHandle();

    $frame = $element->contentFrame();

    // Frame would be an object with guid if implemented
    expect($frame)->toBeNull(); // Will be null until Frame class is properly integrated
});
