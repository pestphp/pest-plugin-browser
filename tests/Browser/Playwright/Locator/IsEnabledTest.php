<?php

declare(strict_types=1);

it('returns true for enabled buttons', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $locator = $page->getByTestId('enabled-button');

    expect($locator->isEnabled())->toBeTrue();
});

it('returns false for disabled buttons', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $locator = $page->getByTestId('disabled-button');

    expect($locator->isEnabled())->toBeFalse();
});
