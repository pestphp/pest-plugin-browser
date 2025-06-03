<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Locator;

describe('getByPlaceholder', function (): void {
    beforeEach(function (): void {
        $this->page = $this->page('/test/selector-tests');
    });

    it('finds an input element by placeholder text', function (): void {
        $element = $this->page->getByPlaceholder('Search...');

        expect($element)->toBeInstanceOf(Locator::class);
        expect($element->getAttribute('type'))->toBe('text');
    });

    it('finds a textarea by placeholder text', function (): void {
        $element = $this->page->getByPlaceholder('Enter your comments here');

        expect($element)->toBeInstanceOf(Locator::class);
        expect($element->isVisible())->toBeTrue();
    });

    it('finds an element with exact matching', function (): void {
        $element = $this->page->getByPlaceholder('Search...', true);

        expect($element)->toBeInstanceOf(Locator::class);
    });
});
