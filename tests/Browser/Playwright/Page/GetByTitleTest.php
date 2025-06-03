<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Locator;

describe('getByTitle', function (): void {
    beforeEach(function (): void {
        $this->page = $this->page('/test/selector-tests');
    });

    it('finds an element by its title attribute', function (): void {
        $element = $this->page->getByTitle('Info Button');

        expect($element)->toBeInstanceOf(Locator::class);
        expect($element->isVisible())->toBeTrue();
    });

    it('finds a link by its title attribute', function (): void {
        $element = $this->page->getByTitle('Help Link');

        expect($element)->toBeInstanceOf(Locator::class);
    });

    it('finds an element with exact matching', function (): void {
        $element = $this->page->getByTitle('User\'s Name', true);

        expect($element)->toBeInstanceOf(Locator::class);
    });
});
