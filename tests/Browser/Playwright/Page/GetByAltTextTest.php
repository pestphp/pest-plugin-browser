<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Locator;

describe('getByAltText', function (): void {
    beforeEach(function (): void {
        $this->page = $this->page('/test/selector-tests');
    });

    it('finds an image by its alt text', function (): void {
        $element = $this->page->getByAltText('Pest Logo');

        expect($element)->toBeInstanceOf(Locator::class);
        expect($element->isVisible())->toBeTrue();
    });

    it('finds another image by its alt text', function (): void {
        $element = $this->page->getByAltText('Another Image');

        expect($element)->toBeInstanceOf(Locator::class);
    });

    it('finds an element with exact matching', function (): void {
        $element = $this->page->getByAltText('Profile Picture', true);

        expect($element)->toBeInstanceOf(Locator::class);
    });
});
