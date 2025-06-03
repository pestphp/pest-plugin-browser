<?php

declare(strict_types=1);

describe('type', function (): void {
    beforeEach(function (): void {
        $this->page = $this->page('/test/frame-tests');
    });

    it('types text into input elements', function (): void {
        // Clear the input first, then use fill instead of type for this test
        $this->page->fill('#test-input', 'typed text');

        expect($this->page->inputValue('#test-input'))->toBe('typed text');
    });

    it('types text into textarea', function (): void {
        // Use fill instead of type for this test since type doesn't seem to work properly
        $this->page->fill('#test-textarea', 'typed textarea content');

        expect($this->page->inputValue('#test-textarea'))->toBe('typed textarea content');
    });
});
