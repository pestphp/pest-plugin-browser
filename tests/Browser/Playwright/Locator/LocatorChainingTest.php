<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Locator;

it('can chain locators', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $nestedElement = $page->getByTestId('profile-section')->getByText('Name');

    expect($nestedElement)->toBeInstanceOf(Locator::class);
});
