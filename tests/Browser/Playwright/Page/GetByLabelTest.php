<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Locator;

describe('getByLabel', function (): void {
    beforeEach(function (): void {
        $this->page = $this->page('/test/selector-tests');
    });

    it('finds an input element by its associated label', function (): void {
        $element = $this->page->getByLabel('Username');

        expect($element)->toBeInstanceOf(Locator::class);
        expect($element->getAttribute('value'))->toBe('johndoe');
    });

    it('finds a password input by its label', function (): void {
        $element = $this->page->getByLabel('Password');

        expect($element)->toBeInstanceOf(Locator::class);
        expect($element->getAttribute('type'))->toBe('password');
    });
});
