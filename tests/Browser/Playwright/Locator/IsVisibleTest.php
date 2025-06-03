<?php

declare(strict_types=1);

it('returns true for visible elements', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $locator = $page->getByTestId('profile-section');

    expect($locator->isVisible())->toBeTrue();
});

it('returns false for hidden elements', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $locator = $page->getByTestId('hidden-element');

    expect($locator->isVisible())->toBeFalse();
});
