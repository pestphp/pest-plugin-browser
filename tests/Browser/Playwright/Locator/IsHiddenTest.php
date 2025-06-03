<?php

declare(strict_types=1);

it('returns false for visible elements', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $locator = $page->getByTestId('profile-section');

    expect($locator->isHidden())->toBeFalse();
});

it('returns true for hidden elements', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $locator = $page->getByTestId('hidden-element');

    expect($locator->isHidden())->toBeTrue();
});
