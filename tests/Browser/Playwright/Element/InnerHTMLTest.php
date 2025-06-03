<?php

declare(strict_types=1);

it('can get inner HTML of elements', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $locator = $page->getByTestId('profile-section');
    $element = $locator->elementHandle();

    $html = $element->innerHTML();
    expect($html)->toBeString();
    expect($html)->toContain('This section has a data-testid attribute');
});
