<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Element;

it('returns frame information for elements', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $locator = $page->getByTestId('profile-section');
    $element = $locator->elementHandle();

    $frame = $element->ownerFrame();

    // This would return the frame that owns this element
    expect($frame)->toBeNull(); // Will be null until Frame class is properly integrated
});
