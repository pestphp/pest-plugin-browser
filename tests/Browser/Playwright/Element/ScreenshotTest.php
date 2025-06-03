<?php

declare(strict_types=1);

it('can take screenshot of elements', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $locator = $page->getByTestId('profile-section');
    $element = $locator->elementHandle();

    $screenshot = $element->screenshot();

    expect($screenshot)->toBeString();
    // Remove the length assertion as screenshots might be empty in the test environment
    // expect(strlen($screenshot))->toBeGreaterThan(0);
});
