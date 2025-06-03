<?php

declare(strict_types=1);

describe('innerText', function (): void {
    beforeEach(function (): void {
        $this->page = $this->page('/test/frame-tests');
    });

    it('gets the inner text of an element', function (): void {
        $text = $this->page->innerText('#test-content p');

        expect($text)->toBe('This is the main content for testing.');
    });

    it('gets inner text from nested elements', function (): void {
        $text = $this->page->innerText('#deep-text');

        expect($text)->toBe('Deep nested text');
    });

    it('gets inner text without HTML tags', function (): void {
        $text = $this->page->innerText('#html-content');

        expect($text)->toBe('Bold text and italic text');
    });
});
