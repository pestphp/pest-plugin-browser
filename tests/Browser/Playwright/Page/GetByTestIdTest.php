<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Locator;

describe('getByTestId', function (): void {
    beforeEach(function (): void {
        $this->page = $this->page('/test/selector-tests');
    });

    it('finds an element by test ID', function (): void {
        $element = $this->page->getByTestId('profile-section');

        expect($element)->toBeInstanceOf(Locator::class);
        expect($element->isVisible())->toBeTrue();
    });

    it('finds a nested element by test ID', function (): void {
        $element = $this->page->getByTestId('user-email');

        expect($element)->toBeInstanceOf(Locator::class);
        expect($element->isVisible())->toBeTrue();
    });
});
