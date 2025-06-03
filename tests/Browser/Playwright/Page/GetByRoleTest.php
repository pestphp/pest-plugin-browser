<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Locator;

describe('getByRole', function (): void {
    beforeEach(function (): void {
        $this->page = $this->page('/test/selector-tests');
    });

    it('finds an element by role with name option', function (): void {
        $element = $this->page->getByRole('button', ['name' => 'Save']);

        expect($element)->toBeInstanceOf(Locator::class);
        expect($element->isVisible())->toBeTrue();
    });

    it('finds a checkbox by role with name option', function (): void {
        $element = $this->page->getByRole('checkbox', ['name' => 'Remember Me']);

        expect($element)->toBeInstanceOf(Locator::class);
    });
});
