<?php

declare(strict_types=1);

describe('querySelectorAll', function (): void {
    beforeEach(function (): void {
        $this->page = $this->page('/test/frame-tests');
    });

    it('returns empty array when no elements match', function (): void {
        $elements = $this->page->querySelectorAll('.non-existent-class');

        expect($elements)->toBeArray();
        expect($elements)->toHaveCount(0);
    });

    it('returns single element in array when one element matches', function (): void {
        $elements = $this->page->querySelectorAll('#unique-element');

        expect($elements)->toBeArray();
        expect($elements)->toHaveCount(1);
        expect($elements[0])->toBeInstanceOf(Pest\Browser\Playwright\Element::class);
    });

    it('returns multiple elements when multiple match', function (): void {
        $elements = $this->page->querySelectorAll('.test-item');

        expect($elements)->toBeArray();
        expect(count($elements))->toBeGreaterThan(1);

        foreach ($elements as $element) {
            expect($element)->toBeInstanceOf(Pest\Browser\Playwright\Element::class);
        }
    });

    it('can find all input elements', function (): void {
        $inputs = $this->page->querySelectorAll('input');

        expect($inputs)->toBeArray();
        expect(count($inputs))->toBeGreaterThan(0);

        foreach ($inputs as $input) {
            expect($input)->toBeInstanceOf(Pest\Browser\Playwright\Element::class);
        }
    });

    it('can find all elements by tag name', function (): void {
        $buttons = $this->page->querySelectorAll('button');

        expect($buttons)->toBeArray();
        expect(count($buttons))->toBeGreaterThan(0);
    });
});
