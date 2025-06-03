<?php

declare(strict_types=1);

describe('inputValue', function (): void {
    beforeEach(function (): void {
        $this->page = $this->page('/test/frame-tests');
    });

    it('gets the value of text inputs', function (): void {
        $value = $this->page->inputValue('#prefilled-input');

        expect($value)->toBe('initial value');
    });

    it('gets empty value for empty inputs', function (): void {
        // First clear the input to ensure it's empty
        $this->page->fill('#test-input', '');
        $value = $this->page->inputValue('#test-input');

        expect($value)->toBe('');
    });

    it('gets value after filling input', function (): void {
        $this->page->fill('#test-input', 'new value');
        $value = $this->page->inputValue('#test-input');

        expect($value)->toBe('new value');
    });
});
